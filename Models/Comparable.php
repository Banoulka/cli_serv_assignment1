<?php

interface Comparable {
    /**
     * @param Comparable $self
     * @param Comparable $other
     *
     * @return Int -1, 0 or 1 Depending on result of comparison
     */
    public static function compareTo(Comparable $self, Comparable $other);
}