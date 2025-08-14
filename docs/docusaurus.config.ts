import type {Config} from '@docusaurus/types';
import type {ThemeConfig} from '@docusaurus/preset-classic';

const config: Config = {
    title: 'sSeo Docs',
    tagline: 'Powerful modules for Evolution CMS',
    url: 'https://seiger.github.io',
    baseUrl: '/sSeo/',
    favicon: 'img/logo.svg',

    // GitHub Pages
    organizationName: 'Seiger',
    projectName: 'sSeo',
    deploymentBranch: 'gh-pages',

    onBrokenLinks: 'throw',
    onBrokenMarkdownLinks: 'warn',

    i18n: {
        defaultLocale: 'en',
        locales: ['en']
    },

    presets: [
        [
            'classic',
            {
                docs: {
                    path: 'pages',
                    routeBasePath: '/',
                    sidebarPath: require.resolve('./sidebars.ts')
                },
                blog: false,
                theme: {
                    customCss: [require.resolve('./src/css/custom.css')]
                }
            }
        ]
    ],

    themeConfig: {
        navbar: {
            title: 'sSeo Docs',
            logo: {
                alt: 'sSeo',
                src: 'img/logo.svg',
                width: 24, height: 24               // можна підкрутити
            },
            items: [
                {type: 'localeDropdown', position: 'right'}
            ]
        }
    } satisfies ThemeConfig
};

export default config;