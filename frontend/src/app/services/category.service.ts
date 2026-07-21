/**
 * HTTP client for category endpoints.
 * GET /api/categories
 */
import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, catchError, shareReplay, throwError } from 'rxjs';
import { env } from '../config/env.config';
import { ApiListResponse, Category } from '../models/news.model';

@Injectable({ providedIn: 'root' })
export class CategoryService {
  private readonly http = inject(HttpClient);
  private readonly apiUrl = env.apiUrl;

  private categories$?: Observable<ApiListResponse<Category>>;
  private menu$?: Observable<ApiListResponse<Category>>;

  getCategories(): Observable<ApiListResponse<Category>> {
    this.categories$ ??= this.http.get<ApiListResponse<Category>>(`${this.apiUrl}/categories`).pipe(
      catchError((error) => throwError(() => error)),
      shareReplay({ bufferSize: 1, refCount: false }),
    );

    return this.categories$;
  }

  getMenu(): Observable<ApiListResponse<Category>> {
    this.menu$ ??= this.http.get<ApiListResponse<Category>>(`${this.apiUrl}/menu`).pipe(
      catchError((error) => throwError(() => error)),
      shareReplay({ bufferSize: 1, refCount: false }),
    );

    return this.menu$;
  }
}
