/**
 * Unit tests for NewsService.
 *
 * Validates HTTP requests, query-parameter construction,
 * in-memory caching behaviour (shareReplay), and cache invalidation
 * via clearCache(). Uses Angular's HttpTestingController to intercept
 * outgoing requests without hitting the real Laravel API.
 */
import { TestBed } from '@angular/core/testing';
import { provideHttpClient } from '@angular/common/http';
import { provideHttpClientTesting, HttpTestingController } from '@angular/common/http/testing';
import { NewsService } from './news.service';
import { PaginatedResponse, NewsArticle } from '../models/news.model';

describe('NewsService', () => {
  let service: NewsService;
  let httpMock: HttpTestingController; // intercepts HTTP calls made by the service

  /** Stub article matching the shape returned by GET /api/news */
  const article: NewsArticle = {
    id: 1,
    title: 'Breaking News',
    slug: 'breaking-news',
    summary: 'Summary',
    content: '<p>Content</p>',
    image_url: null,
    author: 'Reporter',
    published_at: '2026-07-21T08:00:00Z',
    is_featured: true,
    category: {
      id: 10,
      name: 'Politics',
      slug: 'politics',
      sort_order: 1,
    },
  };

  /** Stub paginated wrapper matching Laravel's ResourceCollection JSON */
  const paginatedResponse: PaginatedResponse<NewsArticle> = {
    data: [article],
    links: {
      first: null,
      last: null,
      prev: null,
      next: null,
    },
    meta: {
      current_page: 2,
      last_page: 5,
      per_page: 12,
      total: 60,
    },
  };

  beforeEach(() => {
    // Provide both the real HttpClient and the testing backend
    TestBed.configureTestingModule({
      providers: [provideHttpClient(), provideHttpClientTesting()],
    });

    service = TestBed.inject(NewsService);
    httpMock = TestBed.inject(HttpTestingController);
  });

  afterEach(() => {
    // Fail the test if any unexpected requests were made
    httpMock.verify();
  });

  /**
   * Ensures getNews() sends a GET to /api/news with the correct
   * ?category= and ?page= query parameters.
   */
  it('requests paginated news with category and page params', () => {
    let responseBody: PaginatedResponse<NewsArticle> | undefined;

    // Call the service with a category slug and page number
    service.getNews('politics', 2).subscribe((response) => {
      responseBody = response;
    });

    // Match the outgoing request by method, URL, and query params
    const request = httpMock.expectOne(
      (req) =>
        req.method === 'GET' &&
        req.url.endsWith('/news') &&
        req.params.get('category') === 'politics' &&
        req.params.get('page') === '2',
    );

    // Simulate a successful backend response
    request.flush(paginatedResponse);

    // The subscriber should have received the flushed data
    expect(responseBody).toEqual(paginatedResponse);
  });

  /**
   * Verifies the shareReplay cache: calling getNews() twice with
   * identical arguments must produce only one HTTP request.
   */
  it('caches list requests for the same category and page', () => {
    let firstResponse: PaginatedResponse<NewsArticle> | undefined;
    let secondResponse: PaginatedResponse<NewsArticle> | undefined;

    // Two subscriptions with the same key
    service.getNews('politics', 2).subscribe((response) => {
      firstResponse = response;
    });
    service.getNews('politics', 2).subscribe((response) => {
      secondResponse = response;
    });

    // Only one HTTP request should have been made
    const request = httpMock.expectOne((req) => req.url.endsWith('/news'));
    request.flush(paginatedResponse);

    // No additional request should exist
    httpMock.expectNone((req) => req.url.endsWith('/news'));

    // Both subscribers receive the same cached data
    expect(firstResponse).toEqual(paginatedResponse);
    expect(secondResponse).toEqual(paginatedResponse);
  });

  /**
   * After clearCache(), the same getNewsById() call must produce
   * a fresh HTTP request instead of returning the cached observable.
   */
  it('clears caches so repeated requests hit the API again', () => {
    let responses = 0; // tracks how many times the subscriber fires

    // First call — populates the cache
    service.getNewsById(1).subscribe(() => {
      responses += 1;
    });
    httpMock.expectOne((req) => req.url.endsWith('/news/1')).flush({ data: article });

    // Invalidate all cached observables
    service.clearCache();

    // Second call — should trigger a new HTTP request
    service.getNewsById(1).subscribe(() => {
      responses += 1;
    });
    httpMock.expectOne((req) => req.url.endsWith('/news/1')).flush({ data: article });

    // Both requests should have resolved
    expect(responses).toBe(2);
  });
});
