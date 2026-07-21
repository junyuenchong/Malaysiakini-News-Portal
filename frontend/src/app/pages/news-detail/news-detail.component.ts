/**
 * Single news article page — /news/:id
 * Data from GET /api/news/{id}
 */
import { Component, OnInit, inject, signal } from '@angular/core';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { DatePipe, NgOptimizedImage } from '@angular/common';
import { NewsService } from '../../services/news.service';
import { NewsArticle } from '../../models/news.model';

@Component({
  selector: 'app-news-detail',
  standalone: true,
  imports: [RouterLink, DatePipe, NgOptimizedImage],
  templateUrl: './news-detail.component.html',
  styleUrl: './news-detail.component.css',
})
export class NewsDetailComponent implements OnInit {
  private readonly newsService = inject(NewsService);
  private readonly route = inject(ActivatedRoute);

  article = signal<NewsArticle | null>(null);
  loading = signal(true);
  error = signal<string | null>(null);

  ngOnInit(): void {
    // Re-load when navigating between articles
    this.route.paramMap.subscribe((params) => {
      const id = Number(params.get('id'));
      this.loadArticle(id);
    });
  }

  /** Fetch single article from Laravel API */
  private loadArticle(id: number): void {
    this.loading.set(true);
    this.error.set(null);

    this.newsService.getNewsById(id).subscribe({
      next: (response) => {
        this.article.set(response.data);
        this.loading.set(false);
      },
      error: () => {
        this.error.set('Berita tidak dijumpai.');
        this.loading.set(false);
      },
    });
  }
}
