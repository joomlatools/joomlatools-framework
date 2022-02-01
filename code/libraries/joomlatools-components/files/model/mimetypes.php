<?php
/**
 * Mimetypes Model
 *
 * @author  Ercan Ozkaya <https://github.com/ercanozkaya>
 * @package Koowa\Component\Files
 */

class ComFilesModelMimetypes extends KModelDatabase
{
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->getState()
            ->insert('mimetype', 'string')
            ->insert('extension', 'string');
    }

    protected function _buildQueryWhere(KDatabaseQueryInterface $query)
    {
        parent::_buildQueryWhere($query);

        $state = $this->getState();
        
        if ($state->mimetype) {
            $query->where('mimetype IN :mimetype')->bind(array('mimetype' => (array) $state->mimetype));
        }

        if ($state->extension) {
        	$query->where('extension IN :extension')->bind(array('extension' => (array) $state->extension));
        }
    }
}
