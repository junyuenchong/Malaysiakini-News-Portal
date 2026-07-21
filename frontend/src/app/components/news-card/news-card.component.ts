/**
 * Single news card — image, title, summary, category, date.
 */
import { Component, input } from '@angular/core';
import { RouterLink } from '@angular/router';
import { DatePipe } from '@angular/common';
import { NewsArticle } from '../../models/news.model';

@Component({
  selector: 'app-news-card',
  standalone: true,
  imports: [RouterLink, DatePipe],
  templateUrl: './news-card.component.html',
  styleUrl: './news-card.component.css',
})
export class NewsCardComponent {
  article = input.required<NewsArticle>();
  index = input(0);
}
