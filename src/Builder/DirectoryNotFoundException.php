<?php
/*
 * This file is part of the Docker Build Orchestrator project.
 *
 * (c) OneGuard <contact@oneguard.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Builder;

use Throwable;

class DirectoryNotFoundException extends \RuntimeException {
    private $path;

    public function __construct(string $path, Throwable $previous = null) {
        parent::__construct("Directory '$path' not found", 0, $previous);
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getPath(): string {
        return $this->path;
    }
}
