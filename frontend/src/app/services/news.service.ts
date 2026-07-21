/**
 * HTTP client for news endpoints.
 * GET /api/news
 * GET /api/news/{id}
 * GET /api/categories/{id}/news
 */
import { Injectable, inject } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable, catchError, shareReplay, throwError } from 'rxjs';
import { env } from '../config/env.config';
import { NewsArticle, PaginatedResponse } from '../models/news.model';

@Injectable({ providedIn: 'root' })
export class NewsService {
  private readonly http = inject(HttpClient);
  private readonly apiUrl = env.apiUrl;

  private readonly listCache = new Map<string, Observable<PaginatedResponse<NewsArticle>>>();
  private readonly detailCache = new Map<number, Observable<{ data: NewsArticle }>>();

  getNews(categorySlug?: string, page = 1): Observable<PaginatedResponse<NewsArticle>> {
    const key = `slug:${categorySlug ?? 'all'}|${page}`;
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
      .pipe(
        catchError((error) => throwError(() => error)),
        shareReplay({ bufferSize: 1, refCount: false }),
      );

    this.listCache.set(key, request$);

    return request$;
  }

  getNewsByCategoryId(categoryId: number, page = 1): Observable<PaginatedResponse<NewsArticle>> {
    const key = `id:${categoryId}|${page}`;
    const cached = this.listCache.get(key);

    if (cached) {
      return cached;
    }

    const params = new HttpParams().set('page', String(page));

    const request$ = this.http
      .get<PaginatedResponse<NewsArticle>>(`${this.apiUrl}/categories/${categoryId}/news`, {
        params,
      })
      .pipe(
        catchError((error) => throwError(() => error)),
        shareReplay({ bufferSize: 1, refCount: false }),
      );

    this.listCache.set(key, request$);

    return request$;
  }

  getNewsById(id: number): Observable<{ data: NewsArticle }> {
    const cached = this.detailCache.get(id);

    if (cached) {
      return cached;
    }

    const request$ = this.http
      .get<{ data: NewsArticle }>(`${this.apiUrl}/news/${id}`)
      .pipe(
        catchError((error) => throwError(() => error)),
        shareReplay({ bufferSize: 1, refCount: false }),
      );

    this.detailCache.set(id, request$);

    return request$;
  }

  clearCache(): void {
    this.listCache.clear();
    this.detailCache.clear();
  }
}
