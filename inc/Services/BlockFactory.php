<?php

namespace GHOSTLABS\CYCLE_BLOCK\Services;

use WpOrg\Requests\Exception;

class BlockFactory
{
    /**
     * Built block output stays tier-local — webpack compiles each tier's blocks
     * into its own build/ — so this one root is right in every context.
     */
    private string $blocksDir = GHOSTLABS_CYCLE_BLOCK_PLUGIN_PATH.'build/blocks';
    /** @var list<string> */
    private array $dirBlackList = ['..', '.', '.DS_Store'];

    /**
     * @return list<callable-string>|false The `_theghostlab_` action functions each
     *         block's actions.php defined, or false when there are no blocks.
     * @throws Exception
     */
    public function getActionTags()
    {
        // block-actions/ can be tier-local or shared, so resolve across roots.
        // Assembled builds merge them into one; the monorepo and wp-env do not.
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
                        // array_pop() is null when nothing was defined yet; slicing from 0 is then right.
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

        // array_values last: array_unique leaves key gaps, and the return is a list.
        return array_values( array_unique( array_filter($new_functions, fn($tag) => strpos($tag, '_theghostlab_') !== false ) ) );
    }
}