/**
 * HTTP client for news endpoints.
 * GET /api/news (paginated, optional ?category=slug&page=n)
 * GET /api/news/{id}
 */
import { Injectable, inject } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable, shareReplay } from 'rxjs';
import { env } from '../config/env.config';
import { NewsArticle, PaginatedResponse } from '../models/news.model';

@Injectable({ providedIn: 'root' })
export class NewsService {
  private readonly http = inject(HttpClient);
  private readonly apiUrl = env.apiUrl;

  /** In-memory cache — revisiting the same page/category is instant */
  private readonly listCache = new Map<string, Observable<PaginatedResponse<NewsArticle>>>();
  private readonly detailCache = new Map<number, Observable<{ data: NewsArticle }>>();

  /**
   * Fetch a paginated news list.
   * @param categorySlug Optional category slug filter (from route param)
   * @param page Page number (default 1)
   */
  getNews(categorySlug?: string, page = 1): Observable<PaginatedResponse<NewsArticle>> {
    const key = `${categorySlug ?? 'all'}|${page}`;
    const cached = this.listCache.get(key);

    if (cached) {
      return cached;
    }

    let params = new HttpParams().set('page', String(page));

    if (categorySlug) {
      params = params.set('category', categorySlug);
    }

    const request$ = this.http
      .get<PaginatedResponse<NewsArticle>>(`${this.apiUrl}/news`, { params })
      .pipe(shareReplay({ bufferSize: 1, refCount: false }));

    this.listCache.set(key, request$);

    return request$;
  }

  /** Fetch a single article by ID */
  getNewsById(id: number): Observable<{ data: NewsArticle }> {
    const cached = this.detailCache.get(id);

    if (cached) {
      return cached;
    }

    const request$ = this.http
      .get<{ data: NewsArticle }>(`${this.apiUrl}/news/${id}`)
      .pipe(shareReplay({ bufferSize: 1, refCount: false }));

    this.detailCache.set(id, request$);

    return request$;
  }

  /** Clear caches after mutations (reserved for future write APIs) */
  clearCache(): void {
    this.listCache.clear();
    this.detailCache.clear();
  }
}
