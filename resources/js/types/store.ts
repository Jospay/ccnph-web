export interface Store {
  id: number;
  slug: string;
  name: string;
  description: string;
  is_active: boolean;
  is_official: boolean;
  logo: string | null;
  banner: string | null;
  logo_url: string | null;
  banner_url: string | null;
}
