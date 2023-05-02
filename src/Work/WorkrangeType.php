<?php

namespace Villermen\Toolbox\Work;

enum WorkrangeType: int
{
    case WORK = 0;
    case HOLIDAY = 1;
    case SICK_LEAVE = 2;
    case SPECIAL_LEAVE = 3;
}
