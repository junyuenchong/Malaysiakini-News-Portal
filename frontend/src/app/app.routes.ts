/**
 * Application routes.
 * All news data comes from the Laravel API — no hard-coded content.
 */
import { Routes } from '@angular/router';
import { NewsListComponent } from './pages/news-list/news-list.component';
import { NewsDetailComponent } from './pages/news-detail/news-detail.component';

export const routes: Routes = [
  { path: '', component: NewsListComponent }, // Home — all news
  { path: 'category/:slug', component: NewsListComponent }, // Filtered by category
  { path: 'news/:id', component: NewsDetailComponent }, // Single article
  { path: '**', redirectTo: '' }, // Unknown paths → home
];
