<?php

require_once( 'rendermonitor.php' );

class ImfsPage extends Imfs_AdminPageFramework {

  public $pluginName;
  public $pluginSlug;
  public $domain;
  public $monitors;
  /**
   * @var bool true if the dbms allows reindexing at all.
   */
  public $canReindex = false;
  /**
   * @var bool true if reindexing does not have the 191 prefix index limitation.
   */
  public $unconstrained = false;
  private $db;
  private $dontNavigate;
  private $tabSuffix;

  public function __construct( $slug = index_wp_mysql_for_speed_domain ) {
    parent::__construct();
    $this->domain       = $slug;
    $this->pluginName   = __( 'Index WP MySQL For Speed', $this->domain );
    $this->pluginSlug   = $slug;
    $this->db           = new ImfsDb( index_mysql_for_speed_major_version, index_mysql_for_speed_previous_major_version );
    $this->dontNavigate = __( 'This may take a few minutes. <em>Please do not navigate away from this page while you wait</em>.', $this->domain );
    $this->tabSuffix    = "_m";
  }

  // https://admin-page-framework.michaeluno.jp/tutorials/01-create-a-wordpress-admin-page/

  public function setUp() {
    $this->setRootMenuPage( 'Tools' );

    $pageName = $this->pluginName;
    /* translators: settings page menu text */
    $menuName = __( 'Index MySQL', $this->domain );
    $this->addSubMenuItems(
      [
        'title'      => $pageName,
        'menu_title' => $menuName,
        'page_slug'  => 'imfs_settings',
        'order'      => 31,
        'capability' => 'activate_plugins',

      ]
    );
    $tabs           = [];
    $tabs[]         = [
      'tab_slug' => 'high_performance_keys',
      'title'    => __( 'High-Performance Keys', $this->domain ),
    ];
    $tabs[]         = [
      'tab_slug' => 'monitor_database_operations',
      'title'    => __( 'Monitor Database Operations', $this->domain ),
    ];
    $this->monitors = RenderMonitor::getMonitors();
    foreach ( $this->monitors as $monitor ) {
      $tabs[] = [
        'tab_slug' => $monitor . $this->tabSuffix,
        'title'    => $monitor,
      ];
    }
    $tabs[] = [
      'tab_slug' => 'about',
      'title'    => __( 'About', $this->domain ),
    ];
    $this->addInPageTabs( 'imfs_settings', ...$tabs );
    $this->setPageHeadingTabsVisibility( false );

  }

  /** Render stuff at the top as needed. if the current tab is a monitor, render the header information
   *
   * @param string $sHTML
   *
   * @return string
   * @callback  action content_{position}_{page slug}
   */
  public function content_top_imfs_settings( $sHTML ) {
    $this->enqueueStyles(
      [
        plugins_url( 'assets/imfs.css', __FILE__ ),
      ], 'imfs_settings' );

    $s       = '';
    $monitor = $this->getMonitorName();

    $sHTML = $this->insertHelpTab( $monitor, $sHTML );

    /* renderMonitor doesn't return anything unless we're on a monitor tab */
    if ( $monitor !== false ) {
      $s .= $this->renderMonitor( $monitor, 'top' );
    }

    return $sHTML . $s;
  }

  /** retrieve the current monitor name from the active tab name.
   * @return false|string
   */
  private function getMonitorName() {
    /* See https://wordpress.org/support/topic/when-naming-inpagetabs-with-variables-how-can-i-use-content_pageslug/#post-14924022 */
    $tab = $this->oProp->getCurrentTabSlug();
    $pos = strrpos( $tab, $this->tabSuffix );
    if ( $pos !== strlen( $tab ) - strlen( $this->tabSuffix ) ) {
      return false;
    }
    $monitor = substr( $tab, 0, $pos );
    if ( ! in_array( $monitor, $this->monitors ) ) {
      return false;
    }

    return $monitor;
  }

  /** Edit the APF header HTML to stick in a HELP tab.
   *
   * @param $monitor
   * @param string $sHTML
   *
   * @return string
   */
  private function insertHelpTab( $monitor, $sHTML ) {
    $tabSlug = $monitor ? 'monitor' : $this->oProp->getCurrentTabSlug();
    $helpUrl = index_wp_mysql_for_speed_help_site . $tabSlug;
    $help    = __( 'Help', $this->domain );
    /** @noinspection HtmlUnknownTarget */
    $helpTag = '<a class="helpbutton nav-tab" target="_blank" href="%s">%s</a>';
    $helpTag = sprintf( $helpTag, $helpUrl, $help );

    $delimiter = '<a class=';
    $splits    = explode( $delimiter, $sHTML, 2 );

    return $splits[0] . $helpTag . $delimiter . $splits[1];
  }

  /**
   * present a saved monitor
   *
   * @param string $monitor
   * @param string $part 'top'   or 'bottom'
   *
   * @return string
   */
  private function renderMonitor( $monitor, $part ) {
    $this->enqueueStyles(
      [
        plugins_url( 'assets/datatables/datatables.min.css', __FILE__ ),
      ], 'imfs_settings' );
    $this->enqueueScripts(
      [
        plugins_url( 'assets/datatables/datatables.min.js', __FILE__ ),
        plugins_url( 'assets/imfs.js', __FILE__ ),
      ], 'imfs_settings' );

    return RenderMonitor::renderMonitors( $monitor, $part, $this->db );
  }

  /** Render top of panel
   *
   * @param string $sHTML
   *
   * @return string
   * @callback  action content_{position}_{page slug}
   */
  public function content_top_imfs_settings_high_performance_keys( $sHTML ) {

    return $sHTML . '<div class="index-wp-mysql-for-speed-content-container">' . $this->wpCliAdmonition() . '</div>';
  }

  /** Get header information about wp-cli
   *
   * @return string
   */
  public function wpCliAdmonition() {
    /** @noinspection HtmlUnknownTarget */
    $wpCliUrl = '<a href="https://make.wordpress.org/cli/handbook/">WP-CLI</a>';

    $wpCliString = '<p class="topinfo">' . __( 'This plugin supports %s. <em>Please use it if possible</em>: it avoids web server timeouts when changing keys on large tables.', $this->domain );
    $wpCliString = sprintf( $wpCliString, $wpCliUrl );
    $wpCliString .= ' ' . __( 'To learn more, type', $this->domain ) . ' ' . '<code>wp help index-mysql</code>' . __( 'into your command shell.', $this->domain ) . '</p>';

    return $wpCliString;
  }

  /** Render stuff at the bottom as needed. if the current tab is a monitor, render the data
   *
   * @param string $sHTML
   *
   * @return string
   * @callback  action content_{position}_{page slug}_{tab_slug}
   */
  public
  function content_bottom_imfs_settings(
    $sHTML
  ) {
    $s = '';
    /* renderMointor doesn't return anything unless we're on a monitor tab */
    $monitor = $this->getMonitorName();
    if ( $monitor !== false ) {
      $s .= $this->renderMonitor( $monitor, 'bottom' );
    }

    return $sHTML . $s;
  }

  /** render informational content at the top of the About tab
   *
   * @param string $sHTML
   *
   * @return string
   * @callback  action content_{position}_{page slug}_{tab_slug}
   */
  public
  function content_top_imfs_settings_about(
    $sHTML
  ) {
    /** @noinspection HtmlUnknownTarget */
    $hyperlink     = '<a href="%s" target="_blank">%s</a>';
    $supportUrl    = "https://wordpress.org/support/plugin/index-wp-mysql-for-speed/";
    $helpUrl       = index_wp_mysql_for_speed_help_site;
    $reviewUrl     = "https://wordpress.org/support/plugin/index-wp-mysql-for-speed/reviews/";
    $detailsUrl    = index_wp_mysql_for_speed_help_site . "tables_and_keys/";
    $clickHere     = __( 'click here', $this->domain );
    $orUseHelpTab  = __( 'or use the Help tab in the upper left corner of this page.' );
    $help          = sprintf( $hyperlink, $helpUrl, $clickHere ) . ' ' . $orUseHelpTab;
    $support       = sprintf( $hyperlink, $supportUrl, $clickHere );
    $review        = sprintf( $hyperlink, $reviewUrl, $clickHere );
    $details       = sprintf( $hyperlink, $detailsUrl, $clickHere );
    $helpString    = '<p class="topinfo">' . __( 'For help please %s.', $this->domain ) . '</p>';
    $helpString    = sprintf( $helpString, $help );
    $supportString = '<p class="topinfo">' . __( 'For support please %s. If you create an issue in the support forum, please upload your diagnostic metadata, and mention the id of your upload.  Please %s to rate this plugin.', $this->domain ) . '</p>';
    $supportString = sprintf( $supportString, $support, $review );
    $detailsString = '<p class="topinfo">' . __( 'For detailed information about this plugin\'s actions on your database, please %s.', $this->domain ) . '</p>';
    $detailsString = sprintf( $detailsString, $details );
    $wpCliString   = $this->wpCliAdmonition();

    return $sHTML . '<div class="index-wp-mysql-for-speed-content-container">' . $helpString . $supportString . $detailsString . $wpCliString . '</div>';
  }

  /** Render the form in the rekey tab
   *
   * @param object $oAdminPage
   *
   * @callback  action validation_{page slug}_{tab_slug}
   * @noinspection PhpUnusedParameterInspection
   */
  public function load_imfs_settings_high_performance_keys( $oAdminPage ) {

    $optName = $oAdminPage->oProp->sOptionKey;
    $opts    = get_option( $optName );
    if ( ! $opts ) {
      $opts = [];
    }
    $opts['majorVersion'] = index_mysql_for_speed_major_version;
    update_option( $optName, $opts );

    if ( $this->checkVersionInfo() ) {

      $rekeying = $this->db->getRekeying();

      $this->showIndexStatus( $rekeying );

      /* stash the major version to help with updates */
      $this->addSettingFields(
        [
          'field_id' => 'majorVersion',
          'value'    => index_mysql_for_speed_major_version,
          'type'     => 'hidden',
          'save'     => true,
        ] );


      $this->addSettingFields(
        [
          'field_id' => 'actionmessage',
          'title'    => __( 'Actions', $this->domain ),
          'default'  => __( 'Actions you can take on your tables.', $this->domain ),
          'save'     => false,
          'class'    => [
            'fieldrow' => [ 'major', 'header' ],
          ],
        ] );


      $this->addSettingFields(

        [
          'field_id' => 'backup',
          'title'    => __( 'Backup', $this->domain ),
          'label'    => __( 'This plugin modifies your WordPress database. Make a backup before you proceed.', $this->domain ),
          'save'     => false,
          'class'    => [
            'fieldrow' => 'info',
          ],
          [
            'field_id' => 'backup_done',
            'type'     => 'checkbox',
            'label'    => __( 'I have made a backup', $this->domain ),
            'default'  => 0,
            'save'     => false,
            'class'    => [
              'fieldrow' => 'major',
            ],

          ],
        ]
      );
      /* engine upgrade ***************************/
      $this->upgradeIndex();

      /* rekeying ***************************/
      $action = 'enable';
      if ( count( $rekeying[ $action ] ) > 0 ) {
        $title        = __( 'Add keys', $this->domain );
        $caption      = __( 'Add high-performance keys', $this->domain );
        $callToAction = __( 'Add Keys Now', $this->domain );
        $this->renderListOfTables( $rekeying[ $action ], false, $action, $action, $title, $caption, $callToAction, true );
      }
      /* updating old versions of keys  ***************************/
      $action = 'old';
      if ( count( $rekeying[ $action ] ) > 0 ) {

        $title        = __( 'Update keys', $this->domain );
        $caption      = __( 'Update keys to this plugin\'s latest version', $this->domain );
        $callToAction = __( 'Update Keys Now', $this->domain );
        $this->renderListOfTables( $rekeying[ $action ], false, $action, 'enable', $title, $caption, $callToAction, true );
      }
      /* converting nonstandard keys  ***************************/
      $action = 'nonstandard';
      if ( count( $rekeying[ $action ] ) > 0 ) {

        $title        = __( 'Convert keys', $this->domain );
        $caption      = __( 'Convert to this plugin\'s high-performance keys', $this->domain );
        $callToAction = __( 'Convert Keys Now', $this->domain );
        $this->renderListOfTables( $rekeying[ $action ], false, $action, 'enable', $title, $caption, $callToAction, true );
      }
      /* disabling  ***************************/
      $action = 'disable';
      if ( count( $rekeying[ $action ] ) > 0 ) {

        $title        = __( 'Revert keys', $this->domain );
        $caption      = __( 'Revert to WordPress\'s default keys', $this->domain );
        $callToAction = __( 'Revert Keys Now', $this->domain );
        $this->renderListOfTables( $rekeying[ $action ], false, $action, $action, $title, $caption, $callToAction, false );
      }
    }
    $this->showVersionInfo();
  }

  /** Make sure our MySQL version is sufficient to do all this.
   * @return bool
   */
  private
  function checkVersionInfo() {

    if ( ! $this->db->canReindex ) {
      $this->addSettingFields(
        [
          'field_id'    => 'version_error',
          'title'       => 'Notice',
          'default'     => __( 'Sorry, you cannot use this plugin with your version of MySQL.', $this->domain ),
          'description' => __( 'Your MySQL version is outdated. Please consider upgrading,', $this->domain ),
          'save'        => false,
          'class'       => [
            'fieldrow' => 'failure',
          ],
        ] );
    } else {
      if ( ! $this->db->unconstrained ) {
        $this->addSettingFields(
          [
            'field_id' => 'constraint_notice',
            'title'    => 'Notice',
            'default'  => __( 'Upgrading your MySQL server version will give you better performance when you add high-performance keys. Please consider doing that before you add these keys.', $this->domain ),
            'save'     => false,
            'class'    => [
              'fieldrow' => 'warning',
            ],
          ] );
      }
    }

    return $this->db->canReindex;
  }

  /** present a list of tables with their indexing status.
   *
   * @param array $rekeying
   */
  private
  function showIndexStatus(
    array $rekeying
  ) {
    global $wpdb;
    $messageNumber = 0;
    /* display current status */
    if ( is_array( $rekeying['upgrade'] ) && count( $rekeying['upgrade'] ) > 0 ) {
      $list  = implode( ', ', $rekeying['upgrade'] );
      $label = __( 'These database tables need upgrading to MySQL\'s latest table storage format, InnoDB with dynamic rows.', $this->domain );
      $label .= '<p class="tablelist">' . $list . '</p>';
      $this->addSettingFields(
        [
          'field_id' => 'message' . $messageNumber ++,
          'title'    => __( 'Tables to upgrade', $this->domain ),
          'default'  => $label,
          'save'     => false,
          'class'    => [
            'fieldrow' => [ 'major', 'warning' ],
          ],
        ] );
    }
    if ( count( $rekeying['fast'] ) > 0 ) {
      $list = [];
      foreach ( $rekeying['fast'] as $tbl ) {
        $list[] = $wpdb->prefix . $tbl;
      }
      $list  = implode( ', ', $list );
      $label = __( 'You have added high-performance keys to these tables. You can revert them to WordPress\'s standard keys.', $this->domain );
      $label .= '<p class="tablelist">' . $list . '</p>';
      $this->addSettingFields(
        [
          'field_id' => 'message' . $messageNumber ++,
          'title'    => __( 'Success', $this->domain ),
          'default'  => $label,
          'save'     => false,
          'class'    => [
            'fieldrow' => [ 'major', 'success', 'header' ],
          ],
        ] );
    }
    if ( count( $rekeying['old'] ) > 0 ) {
      $list = [];
      foreach ( $rekeying['old'] as $tbl ) {
        $list[] = $wpdb->prefix . $tbl;
      }
      $list  = implode( ', ', $list );
      $label = __( 'You have added high-performance keys to your tables using an earlier version of this plugin. You can revert them to WordPress\'s standard keys, or update them to the latest high-performance keys.', $this->domain );
      $label .= '<p class="tablelist">' . $list . '</p>';

      $this->addSettingFields(
        [
          'field_id' => 'message' . $messageNumber ++,
          'title'    => __( 'Keys to update', $this->domain ),
          'default'  => $label,
          'save'     => false,
          'class'    => [
            'fieldrow' => [ 'major', 'header' ],
          ],
        ] );
    }
    if ( count( $rekeying['enable'] ) > 0 ) {
      $list = [];
      foreach ( $rekeying['enable'] as $tbl ) {
        $list[] = $wpdb->prefix . $tbl;
      }
      $list  = implode( ', ', $list );
      $label = __( 'These tables have WordPress\'s standard keys. You can add high-performance keys to these tables to make your WordPress database faster.', $this->domain );
      $label .= '<p class="tablelist">' . $list . '</p>';

      /** @noinspection PhpUnusedLocalVariableInspection */
      $this->addSettingFields(
        [
          'field_id' => 'message' . $messageNumber ++,
          'title'    => __( 'Keys to add', $this->domain ),
          'default'  => $label,
          'save'     => false,
          'class'    => [
            'fieldrow' => [ 'major', 'header' ],
          ],
        ] );
    }

    if ( count( $rekeying['nonstandard'] ) > 0 ) {
      $list = [];
      foreach ( $rekeying['nonstandard'] as $tbl ) {
        $list[] = $wpdb->prefix . $tbl;
      }
      $list  = implode( ', ', $list );
      $label = __( 'These tables have keys set some way other than this plugin. You can convert them to this plugin\'s latest high-performance keys or revert them to WordPress\'s standard keys.', $this->domain );
      $label .= '<p class="tablelist">' . $list . '</p>';

      /** @noinspection PhpUnusedLocalVariableInspection */
      $this->addSettingFields(
        [
          'field_id' => 'message' . $messageNumber ++,
          'title'    => __( 'Keys to convert', $this->domain ),
          'default'  => $label,
          'save'     => false,
          'class'    => [
            'fieldrow' => [ 'major', 'header' ],
          ],
        ] );
    }

  }

  /**
   * form for upgrading tables to InnoDB
   */
  private
  function upgradeIndex() {
    if ( count( $this->db->oldEngineTables ) > 0 ) {
      $action       = 'upgrade';
      $title        = '<span class="warning header">' . __( 'Upgrade tables', $this->domain ) . '</span>';
      $caption      = __( 'Upgrade table storage format', $this->domain );
      $callToAction = __( 'Upgrade Storage Now', $this->domain );
      $this->renderListOfTables( $this->db->oldEngineTables, true, $action, $action, $title, $caption, $callToAction, true );
    }
  }

  /** draw a list of tables with checkboxes and controls
   *
   * @param array $tablesToRekey
   * @param bool $prefixed true if $tablesToRekey contains wp_foometa not just foometa
   * @param string $action "enable", "disable", "old" or "revert"
   * @param string $actionToDisplay "enable", "disable", "revert"
   * @param string $title
   * @param string $caption
   * @param string $callToAction button caption
   * @param bool $prechecked items should be prechecked
   */
  private
  function renderListOfTables(
    array $tablesToRekey, $prefixed, $action, $actionToDisplay, $title,
    $caption, $callToAction, $prechecked
  ) {

    global $wpdb;
    $this->addSettingFields(
      [
        'field_id' => $action . 'caption',
        'title'    => $title,
        'default'  => $caption,
        'save'     => false,
        'class'    => [
          'fieldrow' => 'major',
        ],
      ]
    );

    $labels    = [];
    $defaults  = [];
    $tableList = [];
    $prefix    = $prefixed ? '' : $wpdb->prefix;
    foreach ( $tablesToRekey as $tbl ) {
      $unprefixed = ImfsStripPrefix( $tbl );
      $rowcount   = - 1;
      if ( array_key_exists( $tbl, $this->db->stats[1] ) ) {
        $rowcount = $this->db->stats[1][ $tbl ]->count;
      } else if ( array_key_exists( $unprefixed, $this->db->stats[1] ) ) {
        $rowcount = $this->db->stats[1][ $unprefixed ]->count;
      }
      if ( $rowcount > 1 ) {
        $rowcount   = number_format_i18n( $rowcount );
        $itemString = $rowcount . ' ' . __( 'rows, approximately', $this->domain );
      } else if ( $rowcount == 1 ) {
        $itemString = $rowcount . ' ' . __( 'row, approximately', $this->domain );
      } else if ( $rowcount == 0 ) {
        $itemString = __( 'no rows', $this->domain );
      } else {
        $itemString = '';
      }
      if ( strlen( $itemString ) > 0 ) {
        $itemString = ' (' . $itemString . ')';
      }
      $tableList[]      = $prefix . $tbl;
      $labels[ $tbl ]   = $prefix . $tbl . $itemString;
      $defaults[ $tbl ] = $prechecked;
    }

    $this->addSettingFields(
      [
        'field_id'           => $action,
        'type'               => 'checkbox',
        'label'              => $labels,
        'default'            => $defaults,
        'save'               => false,
        'after_label'        => '<br />',
        'select_all_button'  => true,
        'select_none_button' => true,
      ]
    );

    $this->addSettingFields(
      [
        'field_id'    => $action . '_now',
        'type'        => 'submit',
        'save'        => false,
        'value'       => $callToAction,
        'description' => $this->dontNavigate,
        'class'       => [
          'fieldrow' => 'action',
        ],
      ],
      [
        'field_id' => $action . '_wp',
        'label'    => $this->cliMessage(
          $actionToDisplay . ' ' . implode( ' ', $tableList ),
          __( $title, $this->domain ) ),
        'save'     => false,
        'class'    => [
          'fieldrow' => 'info',
        ],
      ]
    );
  }

  /** text string with wp cli instrutions
   *
   * @param string $command cli command string
   * @param string $function description of function to carry out
   *
   * @return string
   */
  private
  function cliMessage(
    $command, $function
  ) {
    //$cliLink = ' <a href="https://make.wordpress.org/cli/handbook/" target="_blank">WP-CLI</a>';
    $cliLink = ' WP-CLI';
    $wp      = 'wp index-mysql';
    $blogid  = get_current_blog_id();
    if ( $blogid > 1 ) {
      $wp .= ' ' . '--blogid=' . $blogid;
    }
    /* translators: %1$s is WP-CLI hyperlink, %2s is 'wp index-mysql',  %3$s describes the function, %4$s is the cli commmand */
    $fmt = __( 'Using %1$s, %2$s: <code>%3$s %4$s</code>', $this->domain );

    return sprintf( $fmt, $cliLink, $function, $wp, $command );
  }

  /**
   * text field showing versions
   */
  private
  function showVersionInfo() {
    global $wp_version;
    global $wp_db_version;
    $versionString = 'Plugin:' . index_wp_mysql_for_speed_VERSION_NUM
                     . '&ensp;MySQL:' . htmlspecialchars( $this->db->semver->version )
                     . '&ensp;WordPress:' . $wp_version
                     . '&ensp;WordPress database:' . $wp_db_version
                     . '&ensp;php:' . phpversion();
    $this->addSettingFields(
      [
        'field_id' => 'version',
        'title'    => __( 'Versions', $this->domain ),
        'default'  => $versionString,
        'save'     => false,
        'class'    => [
          'fieldrow' => 'info',
        ],
      ]
    );
  }

  /** Render the Monitor Database Operations form
   *
   * @param $oAdminPage
   *
   * @callback  action validation_{page slug}_{tab_slug}
   * @noinspection PhpUnusedParameterInspection
   */
  public
  function load_imfs_settings_monitor_database_operations(
    $oAdminPage
  ) {

    $sampleText  = __( 'sampling %d%% of pageviews.', $this->domain );
    $labelText   = [];
    $labelText[] = __( '<p class="longlabel">We can monitor your site\'s use of MySQL for a few minutes to help you understand what runs slowly.', $this->domain );
    $labelText[] = __( 'To capture monitoring from your site, push the', $this->domain );
    $labelText[] = __( 'Start Monitoring', $this->domain );
    $labelText[] = __( 'button after choosing  a name for your monitor and the options you need.</p>', $this->domain );
    $labelText[] = __( '<p class="longlabel">Then use your site and dashboard to do things that may be slow so the plugin can capture them.', $this->domain );
    $labelText[] = __( 'While your monitor is active, the plugin captures database activity on your site,', $this->domain );
    $labelText[] = __( 'both yours and other users\'.</p>', $this->domain );
    $labelText[] = __( '<p class="longlabel">When the monitoring time ends, view your saved monitor to see your site\'s MySQL traffic and identify the slowest operations.</p>', $this->domain );
    $labelText   = implode( ' ', $labelText );

    $this->addSettingFields(
      [
        'field_id' => 'monitoring_parameters',
        'title'    => __( 'Monitoring', $this->domain ),
        'label'    => $labelText,
        'class'    => [
          'fieldrow' => 'info',
        ],
      ] );
    $this->addSettingFields(
      [
        'field_id' => 'monitor_specs',
        'type'     => 'inline_mixed',
        'content'  => [
          [
            'field_id' => 'targets',
            'type'     => 'select',
            'save'     => true,
            'default'  => 3,
            'label'    => [
              3 => __( 'Monitor Dashboard and Site', $this->domain ),
              2 => __( 'Monitor Site Only', $this->domain ),
              1 => __( 'Monitor Dashboard Only', $this->domain ),
            ],
          ],
          [
            'field_id'        => 'duration',
            'type'            => 'number',
            'label_min_width' => '',
            'label'           => __( 'for', $this->domain ),
            'save'            => true,
            'default'         => 5,
            'attributes'      => [
              'min' => 1,
            ],
            'class'           => [
              'fieldset' => 'inline',
              'fieldrow' => 'number',
            ],
          ],
          [
            'field_id' => 'duration_text_minutes',
            'label'    => __( 'minutes', $this->domain ),
            'save'     => false,
          ],
          [
            'field_id'   => 'samplerate',
            'type'       => 'select',
            'save'       => true,
            'default'    => 100,
            'label'      => [
              100 => __( 'capturing all pageviews.', $this->domain ),
              50  => sprintf( $sampleText, 50 ),
              20  => sprintf( $sampleText, 20 ),
              10  => sprintf( $sampleText, 10 ),
              5   => sprintf( $sampleText, 5 ),
              2   => sprintf( $sampleText, 2 ),
              1   => sprintf( $sampleText, 1 ),
            ],
            'attributes' => [
              'title' => __( 'If your site is very busy, chooose a lower sample rate.', $this->domain ),
            ],
          ],
          [
            'field_id' => 'name',
            'type'     => 'text',
            'label'    => __( 'Save into', $this->domain ),
            'save'     => true,
            'default'  => 'monitor',
            'class'    => [
              'fieldset' => 'inline',
              'fieldrow' => 'name',
            ],
          ],
        ],
      ] );

    $this->addSettingFields(
      [
        'field_id' => 'monitoring_starter',
        'label'    => __( 'Monitoring stops automatically.', $this->domain ),
        'save'     => false,
        'class'    => [
          'fieldrow' => 'info',
        ],
        [
          'field_id' => 'start_monitoring_now',
          'type'     => 'submit',
          'save'     => false,
          'value'    => __( 'Start Monitoring', $this->domain ),
          'class'    => [
            'fieldrow' => 'action',
          ],
        ],
//	 TODO add wp cli for monitoring
//              array(
//					'label' => $this->cliMessage( 'monitor --minutes=n', __( 'Monitor', $this->domain ) ),
//					'type'  => 'label',
//					'save'  => false,
//					'class' => array(
//						'fieldrow' => 'info',
//					),
//				),

      ]
    );

    $monLabel = count( $this->monitors ) > 0
      ? __( 'Saved monitors', $this->domain )
      : __( 'No monitors are saved. ', $this->domain );

    $this->addSettingFields(
      [
        'field_id' => 'monitor_headers',
        'title'    => __( 'Monitors', $this->domain ),
        'label'    => $monLabel,
        'save'     => false,
        'class'    => [
          'fieldrow' => 'info',
        ],
      ] );


    foreach ( $this->monitors as $monitor ) {
      $log     = new RenderMonitor( $monitor, $this->db );
      $summary = $log->load()->capturedQuerySummary();
      /** @noinspection HtmlUnknownTarget */
      $monitorText = sprintf( "<a href=\"%s&tab=%s%s\">%s</a> %s",
        admin_url( 'tools.php?page=imfs_settings' ), $monitor, $this->tabSuffix, $monitor, $summary );
      $this->addSettingFields(
        [
          'field_id' => 'monitor_row_' . $monitor,
          'type'     => 'inline_mixed',
          'content'  => [
            [
              'field_id'   => 'delete_' . $monitor . '_now',
              'type'       => 'submit',
              'save'       => false,
              'value'      => 'X',
              'tip'        => __( 'Delete', $this->domain ) . ' ' . $monitor,
              'attributes' => [
                'class' => 'button button_secondary button_delete button_round',
                'title' => __( 'Delete', $this->domain ) . ' ' . $monitor,
              ],
            ],

            [
              'field_id' => $monitor . '_title',
              'default'  => $monitorText,
              'save'     => false,
              'class'    => [
                'fieldrow' => 'info',
              ],
            ],
          ],
        ] );
    }
    $this->showVersionInfo();
  }

  /** @noinspection PhpUnused */

  /** Render the About form (info tab)
   *
   * @param $oAdminPage
   *
   * @callback  action load_{page slug}_{tab_slug}
   * @noinspection PhpUnusedParameterInspection
   */
  public
  function load_imfs_settings_about(
    $oAdminPage
  ) {
    if ( ! $this->db->unconstrained ) {
      $this->addSettingFields(
        [
          'field_id' => 'constraint_notice',
          'title'    => 'Notice',
          'default'  => __( 'Upgrading your MySQL server version will give you better performance when you add high-performance keys.', $this->domain ),
          'save'     => false,
          'class'    => [
            'fieldrow' => 'warning',
          ],
        ] );
    }

    $this->showIndexStatus( $this->db->getRekeying() );
    $this->uploadMetadata();
    $this->showVersionInfo();
  }

  /** @noinspection PhpUnused */

  /**
   * render the upload-metadata form fields.
   */
  function uploadMetadata() {
    $this->addSettingFields(
      [
        'field_id' => 'metadata',
        'title'    => __( 'Diagnostic data', $this->domain ),
        'label'    => __( 'With your permission we upload metadata about your WordPress site to our plugin\'s servers. We cannot identify you or your website from it, and we never sell nor give it to any third party. We use it only to improve this plugin.', $this->domain ),
        'save'     => false,
        'class'    => [
          'fieldrow' => 'info',
        ],
      ],
      [
        'field_id' => 'uploadId',
        'title'    => __( 'Upload id', $this->domain ),
        'label'    => __( 'If you create an issue or contact the authors, please mention this upload id.', $this->domain ),
        'type'     => 'text',
        'save'     => true,
        'default'  => imfsRandomString( 8 ),
        'class'    => [
          'fieldrow' => 'randomid',
        ],
      ],
      [
        'field_id'    => 'upload_metadata_now',
        'type'        => 'submit',
        'save'        => false,
        'value'       => __( 'Upload metadata', $this->domain ),
        'description' => $this->dontNavigate,
        'class'       => [
          'fieldrow' => 'action',
        ],
      ],
      [
        'label' => $this->cliMessage( 'upload_metadata', __( 'Upload metadata', $this->domain ) ),
        'type'  => 'label',
        'save'  => false,
        'class' => [
          'fieldrow' => 'info',
        ],
      ]
    );
  }

  /** @noinspection PhpUnused */

  /** load overall page, used to load monitor items (with variable slug names)
   *
   * @param $oAdminPage
   *
   * @callback  action load_{page slug}
   * @noinspection PhpUnusedParameterInspection
   */
  public
  function load_imfs_settings(
    $oAdminPage
  ) {
    try {
      $this->populate();
    } catch ( ImfsException $ex ) {
      $msg = __( 'Something went wrong inspecting your database', $this->domain ) . ': ' . $ex->getMessage();
      $this->setSettingNotice( $msg );

      return;
    }

    $monitor = $this->getMonitorName();
    if ( $monitor === false ) {
      return;
    }
    $this->populate_monitor_fields( $monitor );
  }

  /**
   * @throws ImfsException
   */
  private
  function populate() {

    $this->db->init();
    $this->canReindex    = $this->db->canReindex;
    $this->unconstrained = $this->db->unconstrained;
  }

  private function populate_monitor_fields( $monitor ) {

    $uploadId = Imfs_AdminPageFramework::getOption( get_class( $this ), 'uploadId', imfsRandomString( 8 ) );
    $this->addSettingFields(
      [
        'field_id' => 'monitor_actions',
        'type'     => 'inline_mixed',
        'content'  => [
          [
            'field_id'   => 'upload_' . $monitor . '_now',
            'type'       => 'submit',
            'save'       => false,
            'value'      => __( 'Upload ', $this->domain ),
            'attributes' => [
              'class' => 'button button_secondary',
              'title' => __( 'Upload this monitor to the plugin\'s servers', $this->domain ),
            ],
            'class'      => [
              'fieldset' => 'inline-buttons-and-text',
            ],
          ],
          [
            'field_id' => 'uploadId',
            'type'     => 'text',
            'save'     => true,
            'label'    => __( 'this saved monitor to the plugin\'s servers using upload id', $this->domain ),
            'default'  => $uploadId,
            'class'    => [
              'fieldset' => 'inline-buttons-and-text',
            ],
          ],
        ],
      ] );
  }

  /** Generic validation routine, only used for monitor tabs with varying names
   *
   * @param $inputs
   * @param $oldInputs
   * @param $oAdminPage
   * @param $submitInfo
   *
   * @return mixed  updated $inputs
   * @callback  filter validation_{page slug}
   * @noinspection PhpUnusedParameterInspection
   */
  function validation_imfs_settings( $inputs, $oldInputs, $oAdminPage, $submitInfo ) {
    $errors  = [];
    $monitor = $this->getMonitorName();

    /* submit from monitor tab? */
    if ( $monitor !== false && isset( $inputs['monitor_actions'] ) ) {
      $button = $submitInfo ['field_id'];
      if ( $button === 'upload_' . $monitor . '_now' ) {
        /* It's the upload button. Check the uploadId */
        if ( ! isset( $inputs['monitor_actions']['uploadId'] ) || strlen( $inputs['monitor_actions']['uploadId'] ) === 0 ) {
          /* reject the bogus uploadId */
          $errors['monitor_actions']['uploadId'] = __( "Please provide an upload id.", $this->domain );
          $this->setFieldErrors( $errors );
          $this->setSettingNotice( __( 'Make corrections and try again.', $this->domain ) );

          return $oldInputs;
        }
        /* put the uploadId at the top level of the stored options */
        $uploadId = $inputs['monitor_actions']['uploadId'];
        unset ( $inputs['monitor_actions'] );
        $inputs ['uploadId'] = $uploadId;

        return $this->action( $submitInfo['field_id'], $inputs, $oldInputs, $oAdminPage, $submitInfo );

      }
    }

    return $inputs;
  }

  private
  function action(
    $button, $inputs, $oldInputs, $factory, $submitInfo
  ) {

    $monitor = $this->getMonitorName();
    if ( $monitor !== false && $button === 'upload_' . $monitor . '_now' ) {
      $button = 'upload_monitor_now';
    }
    try {
      switch ( $button ) {
        case 'start_monitoring_now':
          $qmc     = new QueryMonControl();
          $message = $qmc->start( $inputs['monitor_specs'], $this->db );
          $this->setSettingNotice( $message, 'updated' );
          break;
        case 'upgrade_now':
          $msg = $this->db->upgradeStorageEngine( $this->listFromCheckboxes( $inputs['upgrade'] ) );
          $this->setSettingNotice( $msg, 'updated' );
          break;
        case 'enable_now':
          $msg = $this->db->rekeyTables( 1, $this->listFromCheckboxes( $inputs['enable'] ), index_mysql_for_speed_major_version );
          $this->setSettingNotice( $msg, 'updated' );
          break;
        case 'old_now':
          $msg = $this->db->rekeyTables( 1, $this->listFromCheckboxes( $inputs['old'] ), index_mysql_for_speed_major_version );
          $this->setSettingNotice( $msg, 'updated' );
          break;
        case 'nonstandard_now':
          $msg = $this->db->rekeyTables( 1, $this->listFromCheckboxes( $inputs['nonstandard'] ), index_mysql_for_speed_major_version );
          $this->setSettingNotice( $msg, 'updated' );
          break;
        case 'disable_now':
          $msg = $this->db->rekeyTables( 0, $this->listFromCheckboxes( $inputs['disable'] ), index_mysql_for_speed_major_version );
          $this->setSettingNotice( $msg, 'updated' );
          break;
        case 'upload_metadata_now':
          $id = imfs_upload_stats( $this->db, $inputs['uploadId'] );
          $this->setSettingNotice( __( 'Metadata uploaded to id ', $this->domain ) . $id, 'updated' );
          break;
        case 'upload_monitor_now':
          $mon  = new renderMonitor( $monitor, $this->db );
          $data = $mon->load()->makeUpload();
          $id   = imfs_upload_monitor( $this->db, $inputs['uploadId'], $monitor, $data );
          $msg  = __( 'Monitor %1$s uploaded to id %2$s', $this->domain );
          $msg  = sprintf( $msg, $monitor, $id );
          $this->setSettingNotice( $msg, 'updated' );
          break;
      }

      return $inputs;
    } catch ( ImfsException $ex ) {
      $msg = $ex->getMessage();
      $this->setSettingNotice( $msg );

      return $oldInputs;
    }
  }

  /** @noinspection PhpUnusedParameterInspection */

  private
  function listFromCheckboxes(
    $cbs
  ) {
    $result = [];
    foreach ( $cbs as $name => $val ) {
      if ( $val ) {
        $result[] = $name;
      }
    }

    return $result;
  }

  /** Admin Page Framework validation for rekey tab
   *
   * @param $inputs
   * @param $oldInputs
   * @param $factory
   * @param $submitInfo
   *
   * @return mixed
   * @callback  action validation_{page slug}_{tab_slug}
   */
  function validation_imfs_settings_high_performance_keys( $inputs, $oldInputs, $factory, $submitInfo ) {

    $valid  = true;
    $errors = [];

    if ( ! isset ( $inputs['backup']['1'] ) || ! $inputs['backup']['1'] ) {
      $valid            = false;
      $errors['backup'] = __( 'Please acknowledge that you have made a backup.', $this->domain );
    }

    $action = $submitInfo['field_id'];
    $err    = __( 'Please select at least one table.', $this->domain );
    if ( $action === 'enable_now' ) {
      if ( count( $this->listFromCheckboxes( $inputs['enable'] ) ) === 0 ) {
        $valid            = false;
        $errors['enable'] = $err;
      }
    }
    if ( $action === 'disable_now' ) {
      if ( count( $this->listFromCheckboxes( $inputs['disable'] ) ) === 0 ) {
        $valid             = false;
        $errors['disable'] = $err;
      }
    }
    if ( $action === 'upgrade_now' ) {
      if ( count( $this->listFromCheckboxes( $inputs['upgrade'] ) ) === 0 ) {
        $valid             = false;
        $errors['upgrade'] = $err;
      }
    }

    if ( ! $valid ) {
      $this->setFieldErrors( $errors );
      $this->setSettingNotice( __( 'Make corrections and try again.', $this->domain ) );

      return $oldInputs;
    }

    return $this->action( $submitInfo['field_id'], $inputs, $oldInputs, $factory, $submitInfo );
  }

  /**
   * @param $inputs
   * @param $oldInputs
   * @param $factory
   * @param $submitInfo
   *
   * @return mixed
   * @noinspection PhpUnused
   * @callback  action validation_{page slug}_{tab_slug}
   */
  function validation_imfs_settings_monitor_database_operations( $inputs, $oldInputs, $factory, $submitInfo ) {
    $valid  = true;
    $errors = [];

    foreach ( $inputs as $key => $value ) {
      if ( 0 === strpos( $key, "monitor_row_" ) ) {
        foreach ( $value as $rowkey => $button ) {
          $monitor = preg_replace( "/^delete_(.+)_now$/", "$1", $rowkey );
          if ( in_array( $monitor, $this->monitors ) ) {
            RenderMonitor::deleteMonitor( $monitor );
            $this->monitors = RenderMonitor::getMonitors();
            $message        = __( 'Monitor %s deleted.', $this->domain );
            $message        = sprintf( $message, $monitor );
            $this->setSettingNotice( $message, 'updated' );

            return $oldInputs;
          }
        }
      }
    }

    if ( is_array( $submitInfo ) && $submitInfo['field_id'] === 'start_monitoring_now' ) {
      $monitor = $inputs['monitor_specs']['name'];
      if ( ctype_alnum( $monitor ) === false ) {
        $valid                   = false;
        $errors['monitor_specs'] = __( "Letters and numbers only for your monitor name, please.", $this->domain );
      }
    }

    if ( ! $valid ) {
      $this->setFieldErrors( $errors );
      $this->setSettingNotice( __( 'Make corrections and try again.', $this->domain ) );

      return $oldInputs;
    }

    return $this->action( $submitInfo['field_id'], $inputs, $oldInputs, $factory, $submitInfo );
  }

  /**
   * @param $inputs
   * @param $oldInputs
   * @param $factory
   * @param $submitInfo
   *
   * @return mixed
   * @callback  action validation_{page slug}_{tab_slug}
   */
  function validation_imfs_settings_about( $inputs, $oldInputs, $factory, $submitInfo ) {
    $errors = [];
    if ( isset( $inputs['uploadId'] ) && strlen( $inputs['uploadId'] ) > 0 ) {
      $valid = true;
    } else {
      $errors['uploadId'] = __( "Please provide an upload id.", $this->domain );
      $valid              = false;
    }
    if ( ! $valid ) {
      $this->setFieldErrors( $errors );
      $this->setSettingNotice( __( 'Make corrections and try again.', $this->domain ) );

      return $oldInputs;
    }

    return $this->action( $submitInfo['field_id'], $inputs, $oldInputs, $factory, $submitInfo );
  }
}

new ImfsPage;