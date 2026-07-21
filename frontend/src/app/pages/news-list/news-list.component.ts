/**
 * News listing page — home (/) and category filter (/category/:slug).
 * Data from GET /api/news with pagination.
 */
import { Component, OnInit, inject, signal } from '@angular/core';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { DatePipe } from '@angular/common';
import { NewsService } from '../../services/news.service';
import { NewsArticle } from '../../models/news.model';

@Component({
  selector: 'app-news-list',
  standalone: true,
  imports: [RouterLink, DatePipe],
  templateUrl: './news-list.component.html',
  styleUrl: './news-list.component.css',
})
export class NewsListComponent implements OnInit {
  private readonly newsService = inject(NewsService);
  private readonly route = inject(ActivatedRoute);

  articles = signal<NewsArticle[]>([]);
  loading = signal(true);
  error = signal<string | null>(null);
  currentPage = signal(1);
  lastPage = signal(1);
  categorySlug = signal<string | undefined>(undefined);
  pageTitle = signal('Berita Terkini');

  /** 12 skeleton cards shown while API is loading */
  readonly skeletonItems = Array.from({ length: 12 }, (_, i) => i + 1);

  ngOnInit(): void {
    // Re-load when route changes (home ↔ category)
    this.route.paramMap.subscribe((params) => {
      const slug = params.get('slug') ?? undefined;
      this.categorySlug.set(slug);
      this.pageTitle.set(slug ? this.formatCategoryTitle(slug) : 'Berita Terkini');
      this.loadNews(1);
    });
  }

  /** Fetch news from Laravel API */
  loadNews(page: number): void {
    this.loading.set(true);
    this.error.set(null);

    this.newsService.getNews(this.categorySlug(), page).subscribe({
      next: (response) => {
        this.articles.set(response.data);
        this.currentPage.set(response.meta.current_page);
        this.lastPage.set(response.meta.last_page);
        this.loading.set(false);
      },
      error: () => {
        this.error.set('Gagal memuatkan berita. Sila pastikan API Laravel sedang berjalan.');
        this.loading.set(false);
      },
    });
  }

  /** Go to previous/next page */
  goToPage(page: number): void {
    if (page < 1 || page > this.lastPage()) {
      return;
    }
    this.loadNews(page);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  /** Convert slug "latest-news" → "Latest News" for the page heading */
  private formatCategoryTitle(slug: string): string {
    return slug
      .split('-')
      .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
      .join(' ');
  }
}
