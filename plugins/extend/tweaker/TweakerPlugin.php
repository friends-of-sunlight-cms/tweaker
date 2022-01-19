<?php

namespace SunlightExtend\Tweaker;

use Sunlight\Database\Database as DB;
use Sunlight\Extend;
use Sunlight\Plugin\Action\PluginAction;
use Sunlight\Plugin\ExtendPlugin;
use Sunlight\Router;
use Sunlight\Template;
use Sunlight\User;
use Sunlight\Util\Request;
use Sunlight\WebState;

class TweakerPlugin extends ExtendPlugin
{

    /**
     * Event to display page backlink, returns an interaction with the hierarchy.
     * This is a frequently requested remnant of Sunlight CMS 7.x.
     *
     * @param array $args
     */
    public function onShowBacklink(array $args): void
    {
        if ($this->getConfig()->offsetGet('page_show_backlinks')) {
            global $_index, $_page;

            // pages
            if (($_index->type === WebState::PAGE || $_index->type === WebState::PLUGIN)
                && $_index->backlink === null
                && $_page['node_parent'] !== null
            ) {
                $parent = DB::queryRow("SELECT slug FROM " . DB::table('page') . " WHERE id=" . $_page['node_parent']);
                $_index->backlink = Router::page($_page['node_parent'], $parent['slug']);
            }

            // articles
            if (Template::currentIsArticle() && $_index->backlink === null) {
                $_index->backlink = Router::page($_page['id'], $_page['slug']);
            }
        }
    }

    /**
     * Creating a backlink to any page of the system.
     *
     * Usage: In the page editing, in the 'Plugins' field, fill in 'backlink:' and then the address of the page
     *        where the backlink is going.
     * Example: 'backlink:gallery' (without quotes)
     * @param array $args
     */
    public function onPageEvent(array $args): void
    {
        Extend::reg('tpl.backlink', function ($backlinkArgs) use ($args) {
            if(!isset($args['arg'])){
                return;
            }
            $backlinkArgs['backlink'] = _e(Router::slug($args['arg']));
        });
    }

    /**
     * Event of adding a function buttons to editscripts
     *
     * @param array $args
     */
    public function onPageEditScript(array $args): void
    {
        global $_admin, $new, $editscript_setting_extra;

        $editscript_setting_extra .= "<div class='tweaker-box'>";

        // category
        if ($_admin->currentModule === 'content-editcategory' && !$new) {
            $editscript_setting_extra .= "<a class='button block bigger' href='" . _e(Router::admin('content-articles-edit', ['query' => ['new_cat' => Request::get('id')]])) . "'>
                <img src='" . _e($this->getIcon('page_edit')) . "' alt='new' class='icon'>"
                . _lang('admin.content.articles.create')
                . "</a>";
        }

        // gallery
        if ($_admin->currentModule === 'content-editgallery' && !$new) {
            $editscript_setting_extra .= "<a class='button block bigger' href='" . _e(Router::admin('content-manageimgs', ['query' => ['g' => Request::get('id')]])) . "'>
                <img src='" . _e($this->getIcon('images')) . "' alt='edit' class='icon'>"
                . _lang('admin.content.form.manageimgs')
                . "</a>";

            if (User::hasPrivilege('adminsettings')) {
                $editscript_setting_extra .= "<a class='button block bigger' href='" . _e(Router::admin('settings', ['fragment' => 'settings_galleries'])) . "'>
                <img src='" . _e($this->getIcon('cog')) . "' alt='setting' class='icon'>"
                    . _lang('tweaker.btn.gallery.settings')
                    . "</a>";
            }
        }

        $editscript_setting_extra .= "</div>";
    }

    public function onTweakerStyle($args)
    {
        $args['output'] .= "\n/* Tweaker Plugin */\n";
        $args['output'] .= ".module-content-editgallery>form>p>a.button {display: none;}\n";
        $args['output'] .= "button.block.bigger {margin: 6px;}\n";
        $args['output'] .= "button.block img.icon {float: none; margin: 0; padding: 0 10px 0 0;}\n";
        $args['output'] .= "fieldset:target {border: 1px solid " . $GLOBALS['scheme_bar'] . ";}";
        $args['output'] .= "\n/* /Tweaker Plugin */\n";
    }

    private function getIcon(string $name): string
    {
        return $this->getWebPath() . '/Resources/icons/' . $name . '.png';
    }

    /**
     * ============================================================================
     *  EXTEND CONFIGURATION
     * ============================================================================
     */

    /**
     * Plugin config
     * @return array
     */
    protected function getConfigDefaults(): array
    {
        return [
            'page_show_backlinks' => false,
        ];
    }

    public function getAction(string $name): ?PluginAction
    {
        if ($name === 'config') {
            return new Configuration($this);
        }
        return parent::getAction($name);
    }

}
