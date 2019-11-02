<?php

interface Comparable {
    /**
     * Custom compare to function
     *
     * @param Comparable $self  this object
     * @param Comparable $other other object
     *
     * @return int Int -1, 0 or 1 Depending on result of comparison
     * @throws Exception
     */
    public static function compareTo(Comparable $self, Comparable $other);
}