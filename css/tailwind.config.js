/**
 * Tailwind configuration for sSeo package.
 * Extends the global manager config and adds package‑specific paths/tokens.
 */
const path = require('path');

/**
 * We climb to the root of the project regardless of whether
 * the package is in /packages or /core/vendor.
 *
 * __dirname → .../core/vendor/seiger/sseo/css
 * root → .../ (root Evolution CMS)
 */
const root = path.resolve(__dirname, '../../../../..');
const blade = glob => path.join(__dirname, '../views', glob).replace(/\\/g, '/');

// Path to global design tokens (manager/media/style/common/tailwind.config.js)
let base = {};
try {
    base = require(path.join(
        root,
        'manager/media/style/common/tailwind.config.js'
    ));
} catch (e) {
    console.warn('⚠  Global Tailwind config not found, using local only.');
}

module.exports = {
    ...(base.content || []),

    content: [
        ...((base?.content) || []),
        blade('**/*.blade.php'),
        blade('partials/**/*.blade.php'),
    ],

    theme: {
        extend: {
            ...(base?.theme?.extend || {}),
        },
    },

    corePlugins: {
        ...(base?.corePlugins || {}),
        display: true,
    },
};
