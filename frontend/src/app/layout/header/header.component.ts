/**
 * Site header with logo and category navigation.
 * Menu items are loaded from GET /api/menu (no hard-coded categories).
 */
import { Component, OnInit, inject, signal } from '@angular/core';
import { RouterLink, RouterLinkActive } from '@angular/router';
import { CategoryService } from '../../services/category.service';
import { Category } from '../../models/news.model';

@Component({
  selector: 'app-header',
  standalone: true,
  imports: [RouterLink, RouterLinkActive],
  templateUrl: './header.component.html',
  styleUrl: './header.component.css',
})
export class HeaderComponent implements OnInit {
  private readonly categoryService = inject(CategoryService);

  menuItems = signal<Category[]>([]);
  mobileMenuOpen = signal(false);

  /** Placeholder labels shown while the API menu is loading (prevents layout shift) */
  readonly navPlaceholders = ['Berita Terkini', 'Politik', 'Ekonomi', 'Sukan', 'Opini', 'Video'];

  ngOnInit(): void {
    this.categoryService.getMenu().subscribe({
      next: (response) => this.menuItems.set(response.data),
    });
  }

  /** Toggle hamburger menu on mobile */
  toggleMobileMenu(): void {
    this.mobileMenuOpen.update((open) => !open);
  }

  /** Close menu after a link is clicked (mobile UX) */
  closeMobileMenu(): void {
    this.mobileMenuOpen.set(false);
  }
}
