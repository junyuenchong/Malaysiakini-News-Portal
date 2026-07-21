/**
 * News listing page — home (/) and category filter (/category/:slug).
 */
import { Component, OnInit, inject, signal } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { NewsService } from '../../services/news.service';
import { CategoryService } from '../../services/category.service';
import { NewsCardComponent } from '../../components/news-card/news-card.component';
import { NewsArticle } from '../../models/news.model';

@Component({
  selector: 'app-news-list',
  standalone: true,
  imports: [NewsCardComponent],
  templateUrl: './news-list.component.html',
  styleUrl: './news-list.component.css',
})
export class NewsListComponent implements OnInit {
  private readonly newsService = inject(NewsService);
  private readonly categoryService = inject(CategoryService);
  private readonly route = inject(ActivatedRoute);

  articles = signal<NewsArticle[]>([]);
  loading = signal(true);
  error = signal<string | null>(null);
  currentPage = signal(1);
  lastPage = signal(1);
  categorySlug = signal<string | undefined>(undefined);
  pageTitle = signal('Latest News');

  readonly skeletonItems = Array.from({ length: 12 }, (_, i) => i + 1);

  ngOnInit(): void {
    this.route.paramMap.subscribe((params) => {
      const slug = params.get('slug') ?? undefined;
      this.categorySlug.set(slug);
      this.pageTitle.set(slug ? this.formatCategoryTitle(slug) : 'Latest News');
      this.loadNews(1);
    });
  }

  loadNews(page: number): void {
    this.loading.set(true);
    this.error.set(null);

    const slug = this.categorySlug();

    if (!slug) {
      this.newsService.getNews(undefined, page).subscribe({
        next: (response) => this.handleNewsResponse(response),
        error: () => this.handleNewsError(),
      });

      return;
    }

    this.categoryService.getCategories().subscribe({
      next: (response) => {
        const category = response.data.find((item) => item.slug === slug);

        if (!category) {
          this.articles.set([]);
          this.currentPage.set(1);
          this.lastPage.set(1);
          this.loading.set(false);
          return;
        }

        this.newsService.getNewsByCategoryId(category.id, page).subscribe({
          next: (newsResponse) => this.handleNewsResponse(newsResponse),
          error: () => this.handleNewsError(),
        });
      },
      error: () => this.handleNewsError(),
    });
  }

  goToPage(page: number): void {
    if (page < 1 || page > this.lastPage()) {
      return;
    }

    this.loadNews(page);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  private handleNewsResponse(response: {
    data: NewsArticle[];
    meta: { current_page: number; last_page: number };
  }): void {
    this.articles.set(response.data);
    this.currentPage.set(response.meta.current_page);
    this.lastPage.set(response.meta.last_page);
    this.loading.set(false);
  }

  private handleNewsError(): void {
    this.error.set('Failed to load news. Please ensure the Laravel API is running.');
    this.loading.set(false);
  }

  private formatCategoryTitle(slug: string): string {
    return slug
      .split('-')
      .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
      .join(' ');
  }
}
