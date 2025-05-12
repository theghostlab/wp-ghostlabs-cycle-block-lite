<?php

namespace THEGHOSTLAB\CYCLE\Services;

use WP_Error;
use WpOrg\Requests\Exception;

class BlockFactory
{
    private string $actionsDir = THEGHOSTLAB_CYCLE_PLUGIN_PATH.'block-actions';
    private string $blocksDir = THEGHOSTLAB_CYCLE_PLUGIN_PATH.'build/blocks';
    private array $dirBlackList = ['..', '.', '.DS_Store'];

    /**
     * @throws Exception
     */
    public function getActionTags()
    {
        if( !is_dir($this->actionsDir) ) return false;
        if( !is_dir($this->blocksDir) ) return false;

        $blocks = array_values( array_diff( scandir($this->blocksDir), $this->dirBlackList ) );
        
        if( empty($blocks) ) return false;

        $new_functions = [];
		$errors = [];

        foreach ($blocks as $block)
        {
            $blockActionDir = sprintf('%s/%s',$this->actionsDir,$block);

            if( !is_dir($blockActionDir) ) {
	            $errors[] = new WP_Error('ghostlabs_block_error', "$blockActionDir does not exist!");
            }

            if ($handle = opendir($blockActionDir)) {
                while (false !== ($file = readdir($handle))) {
                    if (in_array($file, $this->dirBlackList)) continue;

                    if ($file === 'actions.php') {
                        $functions = get_defined_functions();
                        $array = array_keys($functions['user']);
                        $last_index = array_pop($array);

                        require_once($blockActionDir . '/actions.php');

                        $functions = get_defined_functions();
                        $new_functions = array_merge(array_slice($functions['user'], $last_index), $new_functions);

                        break;
                    }
                }
                closedir($handle);
            }
        }

		if(!empty($errors)) {
			return $errors;
		}

        return array_unique( array_values( array_filter($new_functions, fn($tag) => strpos($tag, 'ghostlabs') !== false ) ) );
    }
}