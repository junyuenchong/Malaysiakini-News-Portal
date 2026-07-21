/**
 * TypeScript interfaces matching the Laravel API JSON responses.
 */

/** Category from GET /api/categories or /api/menu */
export interface Category {
  id: number;
  name: string;
  slug: string; // URL-friendly filter key, e.g. "world"
  sort_order: number;
}

/** News article from GET /api/news or GET /api/news/{id} */
export interface NewsArticle {
  id: number;
  title: string;
  slug: string;
  summary: string;
  content: string; // HTML body — only on detail endpoint
  image_url: string | null; // Local cached image from Laravel /media/news/{id}.jpg
  author: string;
  published_at: string; // ISO 8601 from API
  is_featured: boolean;
  category: Category;
}

/** Paginated list wrapper from GET /api/news */
export interface PaginatedResponse<T> {
  data: T[];
  links: {
    first: string | null;
    last: string | null;
    prev: string | null;
    next: string | null;
  };
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

/** Simple list wrapper from GET /api/menu and /api/categories */
export interface ApiListResponse<T> {
  data: T[];
}
