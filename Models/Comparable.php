<?php


interface Comparable {
    /**
     * @param Comparable $other
     *
     * @return Int -1, 0 or 1 Depending on result of comparison
     */
    public function compareTo(Comparable $other);
}