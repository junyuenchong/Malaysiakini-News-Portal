/**
 * Navigation bar — menu items from GET /api/categories.
 */
import { Component, OnInit, inject, signal } from '@angular/core';
import { RouterLink, RouterLinkActive } from '@angular/router';
import { CategoryService } from '../../services/category.service';
import { Category } from '../../models/news.model';

@Component({
  selector: 'app-navbar',
  standalone: true,
  imports: [RouterLink, RouterLinkActive],
  templateUrl: './navbar.component.html',
  styleUrl: './navbar.component.css',
})
export class NavbarComponent implements OnInit {
  private readonly categoryService = inject(CategoryService);

  menuItems = signal<Category[]>([]);
  mobileMenuOpen = signal(false);

  readonly navPlaceholders = ['Malaysia', 'World', 'Business', 'Sports', 'Opinion', 'Life'];

  ngOnInit(): void {
    this.categoryService.getCategories().subscribe({
      next: (response) => this.menuItems.set(response.data),
    });
  }

  toggleMobileMenu(): void {
    this.mobileMenuOpen.update((open) => !open);
  }

  closeMobileMenu(): void {
    this.mobileMenuOpen.set(false);
  }
}
