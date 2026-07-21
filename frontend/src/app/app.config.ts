/**
 * Global Angular providers — registered once at app startup.
 */
import { ApplicationConfig, provideZoneChangeDetection } from '@angular/core';
import { provideRouter } from '@angular/router';
import { provideHttpClient, withFetch } from '@angular/common/http';
import { IMAGE_LOADER, ImageLoaderConfig } from '@angular/common';

import { routes } from './app.routes';

/**
 * Custom image loader for NgOptimizedImage.
 * Returns the URL as-is so full paths from the API work (e.g. /media/news/1.jpg).
 */
function imageLoader(config: ImageLoaderConfig): string {
  return config.src;
}

export const appConfig: ApplicationConfig = {
  providers: [
    provideZoneChangeDetection({ eventCoalescing: true }),
    provideRouter(routes),
    provideHttpClient(withFetch()), // Use fetch API for HTTP calls to Laravel
    { provide: IMAGE_LOADER, useValue: imageLoader },
  ],
};
