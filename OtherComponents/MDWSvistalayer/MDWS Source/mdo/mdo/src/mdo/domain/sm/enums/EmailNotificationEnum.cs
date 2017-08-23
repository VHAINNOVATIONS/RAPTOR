using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.domain.sm.enums
{
    [Serializable]
    public enum EmailNotificationEnum
    {
        NONE = 0,
        EACH_MESSAGE = 1,
        ONE_DAILY = 2,
        ON_ASSIGNMENT = 3
    }
}
