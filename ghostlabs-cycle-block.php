<?php
/**
 * Cycle: Lite
 *
 * @package       THEGHOSTLABCYCLE
 * @author        GhostLabs
 * @license       gplv2
 * @version       1.0
 *
 * @wordpress-plugin
 * Plugin Name:   GhostLabs Cycle Block: Lite
 * Plugin URI:    https://github.com/theghostlab/wp-ghostlabs-cycle-block-lite
 * Description:   Have your content update on its own by cycling through content variations.
 * Version:       1.0
 * Author:        GhostLabs
 * Author URI:    https://theghostlab.io
 * Text Domain:   ghostlabs-cycle-block-lite
 * License:       GPLv2
 * License URI:   https://www.gnu.org/licenses/gpl-2.0.html
 *
 * You should have received a copy of the GNU General Public License
 * along with Cycle. If not, see <https://www.gnu.org/licenses/gpl-2.0.html/>.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

require 'GhostLabsCycleLitePsr4AutoloaderClass.php';

define('THEGHOSTLAB_CYCLE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('THEGHOSTLAB_CYCLE_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('THEGHOSTLAB_CYCLE_PLUGIN_SLUG', plugin_basename( __FILE__ ));

$autoloader = new THEGHOSTLAB\CYCLE\SCAFFOLD\GhostLabsCycleLitePsr4AutoloaderClass();
$autoloader->addNamespace('THEGHOSTLAB\CYCLE', 'inc/classes');
$autoloader->addNamespace('THEGHOSTLAB\CYCLE\Handlers', 'inc/classes/Handlers');
$autoloader->addNamespace('THEGHOSTLAB\CYCLE\Models', 'inc/classes/Models');
$autoloader->addNamespace('THEGHOSTLAB\CYCLE\Services', 'inc/classes/Services');
$autoloader->addNamespace('THEGHOSTLAB\CYCLE\Services\Entries', 'inc/classes/Services/Entries');
$autoloader->addNamespace('THEGHOSTLAB\CYCLE\Services\Extras', 'inc/classes/Services/Extras');
$autoloader->addNamespace('THEGHOSTLAB\CYCLE\Services\Save', 'inc/classes/Services/Save');
$autoloader->addNamespace('THEGHOSTLAB\CYCLE\Services\Traits', 'inc/classes/Services/Traits');
//REST ROUTES
$autoloader->addNamespace('THEGHOSTLAB\CYCLE\RestRoutes', 'inc/classes/RestRoutes');

//DTOS
$autoloader->addNamespace('THEGHOSTLAB\CYCLE\DTO', 'inc/classes/DTO');
$autoloader->addNamespace('THEGHOSTLAB\CYCLE\DTO\Input', 'inc/classes/DTO/Input');;
$autoloader->addNamespace('THEGHOSTLAB\CYCLE\DTO\Output', 'inc/classes/DTO/Output');;
$autoloader->addNamespace('THEGHOSTLAB\CYCLE\DTO\Validation', 'inc/classes/DTO/Validation');;
$autoloader->register();

use THEGHOSTLAB\CYCLE\KERNEL;
use THEGHOSTLAB\CYCLE\Services\ActivatorDeactivator;

register_activation_hook(__FILE__, [
    ActivatorDeactivator::class,
    'activate',
]);

register_deactivation_hook(__FILE__, [
    ActivatorDeactivator::class,
    'deactivate',
]);

try {
	KERNEL::getInstance()->init()->run();
} catch ( \WpOrg\Requests\Exception $e ) {
	echo esc_html($e->getMessage());
}