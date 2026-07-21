/**
 * Integration tests for NewsListComponent.
 *
 * These tests render the real standalone component with its template
 * but replace external dependencies (NewsService, ActivatedRoute) with
 * test doubles. This validates that the component correctly:
 *   - Fetches and renders articles from the API response
 *   - Displays pagination state in the DOM
 *   - Reacts to route-parameter changes (category navigation)
 *   - Shows an error message when the API call fails
 */
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ActivatedRoute, convertToParamMap } from '@angular/router';
import { BehaviorSubject, of, throwError } from 'rxjs';
import { NewsListComponent } from './news-list.component';
import { NewsService } from '../../services/news.service';
import { PaginatedResponse, NewsArticle } from '../../models/news.model';

describe('NewsListComponent', () => {
  let fixture: ComponentFixture<NewsListComponent>;
  let component: NewsListComponent;
  let newsService: jasmine.SpyObj<NewsService>; // spy replaces the real HTTP-backed service
  let params$: BehaviorSubject<ReturnType<typeof convertToParamMap>>; // emits route param changes

  /** Stub article used across all specs */
  const article: NewsArticle = {
    id: 1,
    title: 'Breaking News',
    slug: 'breaking-news',
    summary: 'Summary text',
    content: '<p>Content</p>',
    image_url: 'https://example.com/news.jpg',
    author: 'Reporter',
    published_at: '2026-07-21T08:00:00Z',
    is_featured: true,
    category: {
      id: 7,
      name: 'Tech',
      slug: 'tech-news',
      sort_order: 1,
    },
  };

  /** Paginated wrapper matching Laravel's ResourceCollection shape */
  const response: PaginatedResponse<NewsArticle> = {
    data: [article],
    links: {
      first: null,
      last: null,
      prev: null,
      next: null,
    },
    meta: {
      current_page: 1,
      last_page: 3,
      per_page: 12,
      total: 30,
    },
  };

  beforeEach(async () => {
    // BehaviorSubject lets us push new route params mid-test
    params$ = new BehaviorSubject(convertToParamMap({}));

    // Create a Jasmine spy object that stubs getNews()
    newsService = jasmine.createSpyObj<NewsService>('NewsService', ['getNews']);

    await TestBed.configureTestingModule({
      imports: [NewsListComponent], // standalone component — imported, not declared
      providers: [
        { provide: NewsService, useValue: newsService }, // replace real service with spy
        {
          provide: ActivatedRoute,
          useValue: {
            paramMap: params$.asObservable(), // fake route params stream
          },
        },
      ],
    }).compileComponents();

    fixture = TestBed.createComponent(NewsListComponent);
    component = fixture.componentInstance;
  });

  /**
   * Happy-path: verifies that after ngOnInit triggers a getNews() call,
   * the rendered DOM contains the article title, author, and correct
   * pagination text (page 1 of 3).
   */
  it('renders fetched articles and pagination state', () => {
    // Return the stub response when the component calls getNews()
    newsService.getNews.and.returnValue(of(response));

    // Trigger ngOnInit → subscribe to paramMap → call loadNews(1)
    fixture.detectChanges();

    const element = fixture.nativeElement as HTMLElement;

    // Default title when no category slug is present
    expect(component.pageTitle()).toBe('Berita Terkini');

    // Service should have been called once with no category and page 1
    expect(newsService.getNews).toHaveBeenCalledOnceWith(undefined, 1);

    // Article data should be rendered in the news card
    expect(element.querySelector('.news-card__title')?.textContent).toContain('Breaking News');
    expect(element.querySelector('.news-card__author')?.textContent).toContain('Reporter');

    // Pagination label should reflect meta from the API response
    expect(element.querySelector('.pagination span')?.textContent).toContain('Halaman 1 / 3');
  });

  /**
   * Simulates navigating from the home route to a category route,
   * then verifies the component reloads data and shows the error
   * state when the second API call fails.
   */
  it('reloads for a category route and shows the API error state when loading fails', () => {
    // First call (home) succeeds; second call (category) fails
    newsService.getNews.and.returnValues(
      of(response),
      throwError(() => new Error('Request failed')),
    );

    // Initial render — home route with no slug
    fixture.detectChanges();

    // Simulate navigation to /category/tech-news
    params$.next(convertToParamMap({ slug: 'tech-news' }));
    fixture.detectChanges(); // re-render after the new paramMap emission

    const element = fixture.nativeElement as HTMLElement;

    // Page title should be derived from the slug ("tech-news" → "Tech News")
    expect(component.pageTitle()).toBe('Tech News');

    // The service should have been called with the new category slug
    expect(newsService.getNews).toHaveBeenCalledWith('tech-news', 1);

    // The error message from loadNews error handler should be visible
    expect(element.querySelector('.state-message--error')?.textContent).toContain(
      'Gagal memuatkan berita',
    );
  });
});
