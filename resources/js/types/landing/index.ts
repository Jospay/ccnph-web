export interface HeroSectionData {
    image_path?: string | null;
    title?: string | null;
    short_title?: string | null;
    content?: string | null;
}

export interface HeroProps {
    sectionData?: HeroSectionData | null;
}

export interface NavItem {
    id: string;
    label: string;
}

export interface NavProps {
    navLogo?: { 
        icon_path?: string; 
    };
}

export interface TutorialItem {
    id: number | string;
    type: 'video' | 'photo';
    is_highlight?: boolean;
    media_path?: string;
    title?: string;
    subtitle?: string;
    description?: string;
    video_url?: string;
}

export interface ContactInfo {
    phone?: string;
    email?: string;
    address?: string;
    facebook_link?: string | null;
    youtube_link?: string | null;
    instagram_link?: string | null;
    twitter_link?: string | null;
    tiktok_link?: string | null;
}

export interface GalleryItem {
    id: number | string;
    type: 'video' | 'photo';
    is_highlight?: boolean;
    media_path?: string;
    title?: string;
    subtitle?: string;
    description?: string;
}

export interface SectionContent {
    title?: string;
    content?: string;
    icon_path?: string;
}

export interface Mission {
    column_position: number;
    title?: string;
    icon?: string;
    content_list?: string;
}

export interface Testimonial {
    image_path?: string;
    name?: string;
    role?: string;
    body?: string;
}