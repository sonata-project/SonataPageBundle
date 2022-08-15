/*!
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// Any SCSS/CSS you require will output into a single css file (app.css in this case)
import '../scss/app.scss';

// Require jQuery normally
import $ from 'jquery';

// Only using droppable widget from jQuery UI library
import 'jquery-ui/ui/widgets/droppable';

// SonataPage custom scripts
import './composer';
import './treeview';

// Create global $ and jQuery variables to be used outside this script
global.$ = $;
global.jQuery = $;
