/**
 * HTTP client for category endpoints.
 * GET /api/menu — categories shown in the navigation bar
 * GET /api/categories — full category list
 */
import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, shareReplay } from 'rxjs';
import { env } from '../config/env.config';
import { ApiListResponse, Category } from '../models/news.model';

@Injectable({ providedIn: 'root' })
export class CategoryService {
  private readonly http = inject(HttpClient);
  private readonly apiUrl = env.apiUrl;

  /** Cached observables — menu/categories rarely change during a session */
  private menu$?: Observable<ApiListResponse<Category>>;
  private categories$?: Observable<ApiListResponse<Category>>;

  /** Navigation menu items (show_in_menu = true on backend) */
  getMenu(): Observable<ApiListResponse<Category>> {
    this.menu$ ??= this.http
      .get<ApiListResponse<Category>>(`${this.apiUrl}/menu`)
      .pipe(shareReplay({ bufferSize: 1, refCount: false }));

    return this.menu$;
  }

  /** All categories */
  getCategories(): Observable<ApiListResponse<Category>> {
    this.categories$ ??= this.http
      .get<ApiListResponse<Category>>(`${this.apiUrl}/categories`)
      .pipe(shareReplay({ bufferSize: 1, refCount: false }));

    return this.categories$;
  }
}
