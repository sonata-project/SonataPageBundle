/*!
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import Encore from '@symfony/webpack-encore';
import StyleLintPlugin from 'stylelint-webpack-plugin';

Encore.setOutputPath('./src/Resources/public')
  .setPublicPath('.')
  .setManifestKeyPrefix('bundles/sonatapage')

  .cleanupOutputBeforeBuild()
  .enableSassLoader()
  .enablePostCssLoader()
  .enableVersioning(false)
  .enableSourceMaps(false)
  .autoProvidejQuery()
  .disableSingleRuntimeChunk()

  .addExternals({
    jquery: 'jQuery',
  })

  .configureCssMinimizerPlugin((options, MinimizerPlugin) => {
    options.minify = {
      implementation: MinimizerPlugin.cssnanoMinify,
      options: {
        preset: ['default', { discardComments: { removeAll: true } }],
      },
    };
  })

  .addPlugin(
    new StyleLintPlugin({
      context: 'assets/scss',
      emitWarning: true,
    })
  )

  .configureJsMinimizerPlugin((options, MinimizerPlugin) => {
    options.minify = {
      implementation: MinimizerPlugin.terserMinify,
      options: { format: { comments: false } },
    };
    options.extractComments = false;
  })

  .addStyleEntry('frontend', './assets/scss/frontend.scss')
  .addEntry('app', './assets/js/app.js');

export default await Encore.getWebpackConfig();
