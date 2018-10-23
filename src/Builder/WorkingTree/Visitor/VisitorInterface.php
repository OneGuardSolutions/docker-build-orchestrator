<?php
/*
 * This file is part of the Docker Build Orchestrator project.
 *
 * (c) OneGuard <contact@oneguard.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Builder\WorkingTree\Visitor;

use App\Builder\WorkingTree\Repository;
use App\Builder\WorkingTree\Tag;
use App\Builder\WorkingTree\WorkingTree;

interface VisitorInterface {
    function visitWorkingTree(WorkingTree $workingTree);
    function visitRepository(Repository $repository);
    function visitTag(Tag $tag);
}
