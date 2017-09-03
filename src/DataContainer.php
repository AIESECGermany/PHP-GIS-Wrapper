<?php
namespace GISwrapper;

/**
 * Class DataContainer
 * contains data of a GIS endpoint and makes it iterable
 *
 * @author Lukas Ehnle <me@ehnle.fyi>
 * @package GISwrapper
 * @version 0.3
 */
class DataContainer implements \Iterator
{
    protected $data;

    protected $currentPage;

    protected $totalPages;

    // for iterator
    private $position = 0;

    // reference to client for loading additional pages
    private $client;

    /**
     * DataContainer constructor.
     */
    function __construct($json, $client)
    {
        $this->client = $client;

        $this->data = $json->data;
        $this->currentPage = $json->paging->current_page;
        $this->totalPages = $json->paging->total_pages;
    }

    public function rewind() {
        $this->position = 0;
    }

    public function current() {
        return $this->data[$this->position];
    }

    public function key() {
        return $this->position;
    }

    public function next() {
        ++$this->position;
        if(!$this->valid() && $this->currentPage != $this->totalPages){
            $this->loadMore();
        }
    }

    public function valid() {
        return isset($this->data[$this->position]);
    }

    private function merge(DataContainer $container){
        $this->data = array_merge($this->data, $container->data);
        $this->currentPage = $container->currentPage;
        $this->totalPages = $container->totalPages;
    }

    private function loadMore(){
        $this->client->options['parameters']['page'] = ++$this->currentPage;
        $res = $this->client->get($this->client->lastUrl);
        $this->merge($res);
    }
}
