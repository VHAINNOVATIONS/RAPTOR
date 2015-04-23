<?php
// $Id$
/**
 * @file
 * File class for loading raw xml files as Sample data
 * This class is used as a reference implementation of a data engine, but is
 * also used to provide sample data files and reports.
 * @author metzlerd
 *
 */
class FrxFiles extends FrxDataSource {
  private $path;
  public function __construct($conf, $repos_path, $name) {
    parent::__construct($conf, $repos_path, $name);
    $this->comment_prefix = '<!--';
    $this->comment_suffix = '-->';
    $this->block_ext = 'xml';
    list($protocol, $path) = explode('://', $conf['uri'], 2);
    if (!$path) $path = $protocol;
    $this->path = $path;
  }

  public function getName() {
    return 'XML File';
  }


}