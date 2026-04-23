<?php
namespace App\Modules\Admin\Models;

use Exception;

/**
 * Links Model
 *
 * Handles link management operations for the admin panel including CRUD operations,
 * ordering, and automatic icon detection for social media and popular platforms.
 *
 * @package App\Modules\Admin\Models
 * @author  StrataPHP Framework
 * @version 1.0.0
 */
class Links
{
    /** @var mixed Database connection instance */
    protected $db;
    
    /** @var array Configuration array */
    protected $config;

    /**
     * Constructor
     *
     * @param mixed $db Database connection instance
     * @param array $config Configuration array
     */
    public function __construct($db, $config)
    {
        $this->db = $db;
        $this->config = $config;
    }

    /**
     * Swap the order of two links
     *
     * @param int $id1 First link ID
     * @param int $id2 Second link ID
     * @return void
     * @throws Exception If database operation fails
     */
    public function swapOrder($id1, $id2)
    {
        try {
            $link1 = $this->getById($id1);
            $link2 = $this->getById($id2);
            if ($link1 && $link2) {
                $order1 = $link1['order'];
                $order2 = $link2['order'];
                $this->db->query('UPDATE links SET `order` = ? WHERE id = ?', [$order2, $id1]);
                $this->db->query('UPDATE links SET `order` = ? WHERE id = ?', [$order1, $id2]);
            }
        } catch (Exception $e) {
            throw new Exception('Failed to swap link order: ' . $e->getMessage());
        }
    }

    /**
     * Get all links ordered by their display order
     *
     * @return array List of all links
     * @throws Exception If database query fails
     */
    public function getAll()
    {
        try {
            return $this->db->fetchAll('SELECT * FROM links ORDER BY `order` ASC');
        } catch (Exception $e) {
            throw new Exception('Failed to retrieve links: ' . $e->getMessage());
        }
    }

    /**
     * Get a specific link by ID
     *
     * @param int $id Link ID
     * @return array|null Link data or null if not found
     * @throws Exception If database query fails
     */
    public function getById($id)
    {
        try {
            return $this->db->fetch('SELECT * FROM links WHERE id = ?', [$id]);
        } catch (Exception $e) {
            throw new Exception('Failed to retrieve link: ' . $e->getMessage());
        }
    }
    
    /**
     * Add a new link
     *
     * @param string $title Link title
     * @param string $url Link URL
     * @param string $icon FontAwesome icon class
     * @param int $nsfw NSFW flag (0 or 1)
     * @return void
     * @throws Exception If database operation fails
     */
    public function addLink($title, $url, $icon, $nsfw = 0)
    {
        try {
            if ($icon === '') {
                $icon = $this->detectIcon($url);
            }
            $this->db->query('INSERT INTO links (title, url, icon, nsfw) VALUES (?, ?, ?, ?)', [$title, $url, $icon, $nsfw]);
        } catch (Exception $e) {
            throw new Exception('Failed to add link: ' . $e->getMessage());
        }
    }
    
    /**
     * Update an existing link
     *
     * @param int $id Link ID
     * @param string $title Link title
     * @param string $url Link URL
     * @param string $icon FontAwesome icon class
     * @param int $nsfw NSFW flag (0 or 1)
     * @return void
     * @throws Exception If database operation fails
     */
    public function updateLink($id, $title, $url, $icon, $nsfw = 0)
    {
        try {
            if ($icon === '') {
                $icon = $this->detectIcon($url);
            }
            $this->db->query('UPDATE links SET title = ?, url = ?, icon = ?, nsfw = ? WHERE id = ?', [$title, $url, $icon, $nsfw, $id]);
        } catch (Exception $e) {
            throw new Exception('Failed to update link: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete a link
     *
     * @param int $id Link ID
     * @return void
     * @throws Exception If database operation fails
     */
    public function deleteLink($id)
    {
        try {
            $this->db->query('DELETE FROM links WHERE id = ?', [$id]);
        } catch (Exception $e) {
            throw new Exception('Failed to delete link: ' . $e->getMessage());
        }
    }
    
    /**
     * Detect appropriate FontAwesome icon based on URL
     *
     * @param string $url The URL to analyze
     * @return string FontAwesome icon class
     */
    public function detectIcon($url)
    {
        $map = [
            'twitter.com' => 'fab fa-twitter',
            'x.com' => 'fab fa-twitter',
            'facebook.com' => 'fab fa-facebook',
            'instagram.com' => 'fab fa-instagram',
            'linkedin.com' => 'fab fa-linkedin',
            'youtube.com' => 'fab fa-youtube',
            'github.com' => 'fab fa-github',
            'tiktok.com' => 'fab fa-tiktok',
            'reddit.com' => 'fab fa-reddit',
            'discord.com' => 'fab fa-discord',
            'pinterest.com' => 'fab fa-pinterest',
            'snapchat.com' => 'fab fa-snapchat',
            'medium.com' => 'fab fa-medium',
            'dribbble.com' => 'fab fa-dribbble',
            'behance.net' => 'fab fa-behance',
            'soundcloud.com' => 'fab fa-soundcloud',
            'spotify.com' => 'fab fa-spotify',
            'tumblr.com' => 'fab fa-tumblr',
            'stack-overflow.com' => 'fab fa-stack-overflow',
            'stackexchange.com' => 'fab fa-stack-exchange',
            'wordpress.com' => 'fab fa-wordpress',
            'telegram.me' => 'fab fa-telegram',
            'telegram.org' => 'fab fa-telegram',
            'whatsapp.com' => 'fab fa-whatsapp',
            'slack.com' => 'fab fa-slack',
            'flickr.com' => 'fab fa-flickr',
            'vimeo.com' => 'fab fa-vimeo',
            'paypal.com' => 'fab fa-paypal',
            'amazon.com' => 'fab fa-amazon',
            'apple.com' => 'fab fa-apple',
            'google.com' => 'fab fa-google',
            'microsoft.com' => 'fab fa-microsoft',
            'threads.com' => 'fa-brands fa-threads',
        ];
        $host = '';
        $parts = parse_url(strtolower($url));
        if (isset($parts['host'])) {
            $host = $parts['host'];
        }
        foreach ($map as $domain => $fa) {
            if ($host === $domain || str_ends_with($host, '.' . $domain)) {
                return $fa;
            }
        }
        return 'fas fa-link'; // fallback generic icon
    }
}
