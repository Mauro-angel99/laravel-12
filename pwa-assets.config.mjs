import { defineConfig, createAppleSplashScreens } from '@vite-pwa/assets-generator/config';

export default defineConfig({
  headLinkOptions: {
    preset: '2023',
  },
  preset: {
    transparent: {
      sizes: [64, 192, 512],
      favicons: [[48, 'favicon-48x48.png']],
    },
    maskable: {
      sizes: [512],
    },
    apple: {
      sizes: [180],
    },
  },
  images: ['public/images/copam-metall-logo.png'],
});
