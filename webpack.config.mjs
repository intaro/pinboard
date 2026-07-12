// Webpack Encore 7 is ESM-only, so this config uses ESM syntax and the .mjs
// extension (the project's package.json has no "type": "module").
import path from 'path';
import Encore from '@symfony/webpack-encore';

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on the webpack config file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    // only needed for CDN's or subdirectory deploy
    //.setManifestKeyPrefix('build/')

    /*
     * ENTRY CONFIG
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */
    .addEntry('app', './assets/app.js')

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    // configure Babel: core-js polyfills on demand. Babel 8 removed the
    // useBuiltIns/corejs options from @babel/preset-env in favour of this plugin.
    .configureBabel((config) => {
        config.plugins.push([
            'babel-plugin-polyfill-corejs3',
            { method: 'usage-global', version: '3.49' },
        ]);
    })

    // enables Sass/SCSS support
    .enableSassLoader((options) => {
        options.api = 'modern';
        options.sassOptions = {
            ...(options.sassOptions || {}),
            loadPaths: [
                path.resolve(import.meta.dirname, 'node_modules'),
            ],
            quietDeps: true,
            style: Encore.isProduction() ? 'compressed' : 'expanded',
        };
    })

    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    // uncomment if you use React
    //.enableReactPreset()

    // uncomment to get integrity="..." attributes on your script & link tags
    // requires WebpackEncoreBundle 1.4 or higher
    //.enableIntegrityHashes(Encore.isProduction())
;

export default await Encore.getWebpackConfig();
