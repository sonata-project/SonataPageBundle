/*!
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

const Encore = require('@symfony/webpack-encore');
const StyleLintPlugin = require('stylelint-webpack-plugin');

Encore.setOutputPath('./src/Resources/public')
  .setPublicPath('.')
  .setManifestKeyPrefix('bundles/sonatapage')

  .cleanupOutputBeforeBuild()
  .enableSassLoader()
  .enablePostCssLoader()
  .enableVersioning(false)
  .enableSourceMaps(false)
  .enableEslintPlugin()
  .autoProvidejQuery()
  .disableSingleRuntimeChunk()

  .addExternals({
    jquery: 'jQuery',
  })

  .configureCssMinimizerPlugin((options) => {
    options.minimizerOptions = {
      preset: ['default', { discardComments: { removeAll: true } }],
    };
  })

  .addPlugin(
    new StyleLintPlugin({
      context: 'assets/scss',
      emitWarning: true,
    })
  )

  .configureTerserPlugin((options) => {
    options.terserOptions = {
      output: { comments: false },
    };
    options.extractComments = false;
  })

  .addStyleEntry('frontend', './assets/scss/frontend.scss')
  .addEntry('app', './assets/js/app.js');

module.exports = Encore.getWebpackConfig();
