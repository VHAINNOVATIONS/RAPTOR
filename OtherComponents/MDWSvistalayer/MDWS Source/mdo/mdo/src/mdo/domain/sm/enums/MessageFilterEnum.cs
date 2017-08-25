using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.domain.sm.enums
{
    // TBD - what are the correct values here? See only 0's in SM database
    [Serializable]
    public enum MessageFilterEnum
    {
        ALL,
        ASSIGNED_TO_ME,
        UNASSIGNED
    }
}
