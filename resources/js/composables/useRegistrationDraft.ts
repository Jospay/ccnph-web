import type { InertiaForm } from '@inertiajs/vue3';
import { onMounted, watch } from 'vue';
import type { CooperativeRegistrationForm } from '@/types/cooperative-registration';

const STORAGE_KEY = 'cooperative_registration_draft_v1';
const DB_NAME = 'cooperative_registration_draft';
const DB_STORE = 'files';

type FileField = 'logo' | 'cda_certificate';
type TextField = Exclude<
  keyof CooperativeRegistrationForm,
  FileField | 'password' | 'password_confirmation'
>;

const FILE_FIELDS: FileField[] = ['logo', 'cda_certificate'];
const TEXT_FIELDS: TextField[] = [
  'cooperative_name',
  'acronym',
  'cda_registration_number',
  'cooperative_type',
  'coop_email',
  'coop_contact',
  'region',
  'province',
  'city',
  'barangay',
  'street_address',
  'zip_code',
  'first_name',
  'last_name',
  'position',
  'email',
  'contact',
];

type Snapshot = Partial<Record<TextField, string>> & { _files?: FileField[] };

// ---------- IndexedDB helpers (all failures are swallowed: the draft is best-effort) ----------

function openDb(): Promise<IDBDatabase | null> {
  return new Promise((resolve) => {
    if (typeof indexedDB === 'undefined') {
      return resolve(null);
    }

    try {
      const request = indexedDB.open(DB_NAME, 1);
      request.onupgradeneeded = () =>
        request.result.createObjectStore(DB_STORE);
      request.onsuccess = () => resolve(request.result);
      request.onerror = () => resolve(null);
    } catch {
      resolve(null);
    }
  });
}

async function withStore<T>(
  mode: IDBTransactionMode,
  run: (store: IDBObjectStore) => IDBRequest<T>,
): Promise<T | null> {
  const db = await openDb();

  if (!db) {
    return null;
  }

  return new Promise((resolve) => {
    try {
      const tx = db.transaction(DB_STORE, mode);
      const request = run(tx.objectStore(DB_STORE));
      request.onsuccess = () => resolve(request.result);
      request.onerror = () => resolve(null);
      tx.oncomplete = () => db.close();
      tx.onabort = () => {
        db.close();
        resolve(null);
      };
    } catch {
      db.close();
      resolve(null);
    }
  });
}

const saveFile = (field: FileField, file: File) =>
  withStore('readwrite', (store) => store.put(file, field));
const loadFile = (field: FileField) =>
  withStore<unknown>('readonly', (store) => store.get(field));
const removeFile = (field: FileField) =>
  withStore('readwrite', (store) => store.delete(field));
const clearFiles = () => withStore('readwrite', (store) => store.clear());

function readSnapshot(): Snapshot | null {
  try {
    const raw = localStorage.getItem(STORAGE_KEY);

    return raw ? (JSON.parse(raw) as Snapshot) : null;
  } catch {
    return null;
  }
}

// ---------- composable ----------

export function useRegistrationDraft(
  form: InertiaForm<CooperativeRegistrationForm>,
) {
  const state = form as unknown as Record<string, unknown>;

  // 1) Restore text fields synchronously, before the child sections mount
  //    (the address section restores its dropdowns from these names).
  const saved = readSnapshot();

  if (saved) {
    TEXT_FIELDS.forEach((key) => {
      if (typeof saved[key] === 'string') {
        state[key] = saved[key];
      }
    });
  }

  // 2) Restore files once mounted.
  onMounted(async () => {
    for (const field of FILE_FIELDS) {
      // Re-read each time: clear() may have run in the meantime.
      if (!readSnapshot()?._files?.includes(field) || state[field]) {
        continue;
      }

      const stored = await loadFile(field);

      if (stored instanceof Blob && !state[field]) {
        state[field] =
          stored instanceof File
            ? stored
            : new File([stored], field, { type: stored.type });
      }
    }
  });

  // 3) Save (debounced).
  let timer: ReturnType<typeof setTimeout> | undefined;

  const scheduleSave = () => {
    clearTimeout(timer);
    timer = setTimeout(() => {
      try {
        const snapshot: Snapshot = {};
        TEXT_FIELDS.forEach((key) => {
          snapshot[key] = String(state[key] ?? '');
        });
        snapshot._files = FILE_FIELDS.filter((field) => !!state[field]);

        const hasData =
          TEXT_FIELDS.some((key) => snapshot[key] !== '') ||
          snapshot._files.length > 0;

        if (hasData) {
          localStorage.setItem(STORAGE_KEY, JSON.stringify(snapshot));
        } else {
          localStorage.removeItem(STORAGE_KEY);
        }
      } catch {
        // storage full / blocked: ignore
      }
    }, 300);
  };

  watch(() => TEXT_FIELDS.map((key) => state[key]), scheduleSave);

  FILE_FIELDS.forEach((field) => {
    watch(
      () => state[field],
      (file) => {
        if (file instanceof File) {
          void saveFile(field, file);
        } else {
          void removeFile(field);
        }

        scheduleSave();
      },
    );
  });

  // 4) Wipe everything (call after a successful registration).
  const clear = async () => {
    clearTimeout(timer);

    try {
      localStorage.removeItem(STORAGE_KEY);
    } catch {
      // ignore
    }

    await clearFiles();
  };

  return { clear };
}
