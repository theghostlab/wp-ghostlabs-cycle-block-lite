<?php

namespace GHOSTLABS\CYCLE_BLOCK\Services;

use WpOrg\Requests\Exception;

if ( ! defined( 'ABSPATH' ) ) exit;

class BlockFactory
{
    
    private string $blocksDir = GHOSTLABS_CYCLE_BLOCK_PLUGIN_PATH.'build/blocks';
    
    private array $dirBlackList = ['..', '.', '.DS_Store'];

    public function getActionTags()
    {

        $actionRoots = SourceRoots::dirs('block-actions');

        if( empty($actionRoots) ) return false;
        if( !is_dir($this->blocksDir) ) return false;

        $blocks = array_values( array_diff( scandir($this->blocksDir), $this->dirBlackList ) );
        
        if( empty($blocks) ) return false;

        $new_functions = [];

        foreach ($blocks as $block)
        {
            $blockActionDir = null;

            foreach ($actionRoots as $root) {
                if (is_dir($root.'/'.$block)) {
                    $blockActionDir = $root.'/'.$block;
                    break;
                }
            }

            if( $blockActionDir === null ) {
                throw new Exception(
                    sprintf('block-actions/%s does not exist in any of: %s', $block, implode(', ', $actionRoots)),
                    'blockfactory'
                );
            }

            if ($handle = opendir($blockActionDir)) {
                while (false !== ($file = readdir($handle))) {
                    if (in_array($file, $this->dirBlackList)) continue;

                    if ($file === 'actions.php') {
                        $functions = get_defined_functions();
                        $array = array_keys($functions['user']);
                        
                        $last_index = array_pop($array) ?? 0;

                        require_once($blockActionDir . '/actions.php');

                        $functions = get_defined_functions();
                        $new_functions = array_merge(array_slice($functions['user'], $last_index), $new_functions);

                        break;
                    }
                }
                closedir($handle);
            }
        }

        return array_values( array_unique( array_filter($new_functions, fn($tag) => strpos($tag, '_theghostlab_') !== false ) ) );
    }
}