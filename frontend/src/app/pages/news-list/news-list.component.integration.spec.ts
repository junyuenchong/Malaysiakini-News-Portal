/**
 * Integration tests for NewsListComponent.
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
  let newsService: jasmine.SpyObj<NewsService>;
  let params$: BehaviorSubject<ReturnType<typeof convertToParamMap>>;

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
      name: 'World',
      slug: 'world',
      sort_order: 1,
    },
  };

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
    params$ = new BehaviorSubject(convertToParamMap({}));

    newsService = jasmine.createSpyObj<NewsService>('NewsService', ['getNews']);

    await TestBed.configureTestingModule({
      imports: [NewsListComponent],
      providers: [
        { provide: NewsService, useValue: newsService },
        {
          provide: ActivatedRoute,
          useValue: {
            paramMap: params$.asObservable(),
          },
        },
      ],
    }).compileComponents();

    fixture = TestBed.createComponent(NewsListComponent);
    component = fixture.componentInstance;
  });

  it('renders fetched articles and pagination state', () => {
    newsService.getNews.and.returnValue(of(response));

    fixture.detectChanges();

    const element = fixture.nativeElement as HTMLElement;

    expect(component.pageTitle()).toBe('Latest News');
    expect(newsService.getNews).toHaveBeenCalledOnceWith(undefined, 1);
    expect(element.querySelector('.news-card__title')?.textContent).toContain('Breaking News');
    expect(element.querySelector('.news-card__author')?.textContent).toContain('Reporter');
    expect(element.querySelector('.pagination span')?.textContent).toContain('Page 1 / 3');
  });

  it('reloads for a category route and shows the API error state when loading fails', () => {
    newsService.getNews.and.returnValue(throwError(() => new Error('Request failed')));

    fixture.detectChanges();

    params$.next(convertToParamMap({ slug: 'world' }));
    fixture.detectChanges();

    const element = fixture.nativeElement as HTMLElement;

    expect(component.pageTitle()).toBe('World');
    expect(newsService.getNews).toHaveBeenCalledWith('world', 1);
    expect(element.querySelector('.state-message--error')?.textContent).toContain('Failed to load news');
  });
});
