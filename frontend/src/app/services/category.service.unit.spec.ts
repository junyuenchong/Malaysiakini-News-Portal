/**
 * Unit tests for CategoryService.
 *
 * Covers the two read-only endpoints consumed by the navigation bar
 * and any future category listing page:
 *   - GET /api/menu      (getMenu)
 *   - GET /api/categories (getCategories)
 *
 * Each test uses HttpTestingController to intercept requests
 * and verify caching behaviour driven by shareReplay.
 */
import { TestBed } from '@angular/core/testing';
import { provideHttpClient } from '@angular/common/http';
import { provideHttpClientTesting, HttpTestingController } from '@angular/common/http/testing';
import { CategoryService } from './category.service';
import { ApiListResponse, Category } from '../models/news.model';

describe('CategoryService', () => {
  let service: CategoryService;
  let httpMock: HttpTestingController; // intercepts outgoing HTTP requests

  /** Stub response matching the Laravel /api/menu and /api/categories JSON */
  const categories: ApiListResponse<Category> = {
    data: [
      {
        id: 1,
        name: 'World',
        slug: 'world',
        sort_order: 1,
      },
      {
        id: 2,
        name: 'Sports',
        slug: 'sports',
        sort_order: 2,
      },
    ],
  };

  beforeEach(() => {
    // Wire up the real HttpClient with a testing backend
    TestBed.configureTestingModule({
      providers: [provideHttpClient(), provideHttpClientTesting()],
    });

    service = TestBed.inject(CategoryService);
    httpMock = TestBed.inject(HttpTestingController);
  });

  afterEach(() => {
    // Ensures no unexpected or unhandled requests remain
    httpMock.verify();
  });

  /**
   * getMenu() uses shareReplay internally — calling it twice
   * must only produce a single GET /api/menu request.
   */
  it('caches the menu response', () => {
    let firstResponse: ApiListResponse<Category> | undefined;
    let secondResponse: ApiListResponse<Category> | undefined;

    // Subscribe twice with the same method
    service.getMenu().subscribe((response) => {
      firstResponse = response;
    });
    service.getMenu().subscribe((response) => {
      secondResponse = response;
    });

    // Only one request should have been dispatched
    const request = httpMock.expectOne((req) => req.method === 'GET' && req.url.endsWith('/menu'));
    request.flush(categories); // simulate successful API response

    // No duplicate request should exist
    httpMock.expectNone((req) => req.url.endsWith('/menu'));

    // Both subscribers should receive identical data from the cache
    expect(firstResponse).toEqual(categories);
    expect(secondResponse).toEqual(categories);
  });

  /**
   * Ensures getCategories() hits the correct /api/categories endpoint
   * and returns the full category list.
   */
  it('requests the full category list from the categories endpoint', () => {
    let responseBody: ApiListResponse<Category> | undefined;

    // Subscribe to the categories observable
    service.getCategories().subscribe((response) => {
      responseBody = response;
    });

    // Verify the request targets the correct URL
    const request = httpMock.expectOne(
      (req) => req.method === 'GET' && req.url.endsWith('/categories'),
    );
    request.flush(categories); // simulate backend response

    // The subscriber should have received the flushed payload
    expect(responseBody).toEqual(categories);
  });
});
