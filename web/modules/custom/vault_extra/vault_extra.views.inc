<?php

/**
 * @file
 * Views hooks for the Vault Extra module.
 *
 * Exposes three computed fields (favorite toggle link, share link, and
 * owner storage usage) so they can be added to node-based Views like the
 * "My Files" and "Admin - All Vault Files" views shipped with this site.
 */

/**
 * Implements hook_views_data().
 */
function vault_extra_views_data() {
  $data['node_field_data']['vault_extra_favorite_toggle'] = [
    'title' => t('Favorite toggle link'),
    'help' => t('Adds a link to favorite/unfavorite this vault file for the current user.'),
    'field' => [
      'id' => 'vault_extra_favorite_toggle',
    ],
  ];

  $data['node_field_data']['vault_extra_share_link'] = [
    'title' => t('Share link'),
    'help' => t('Adds a link to share this vault file with another registered user (owner only).'),
    'field' => [
      'id' => 'vault_extra_share_link',
    ],
  ];

  $data['node_field_data']['vault_extra_storage_usage'] = [
    'title' => t("Owner's storage usage"),
    'help' => t("Shows the file owner's total storage usage against their quota."),
    'field' => [
      'id' => 'vault_extra_storage_usage',
    ],
  ];

  return $data;
}
