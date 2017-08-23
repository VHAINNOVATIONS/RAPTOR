using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.domain.sm.enums
{
    // TBD - should user status be IPA, NON_IPA or similar? These statuses came from SM database
    [Serializable]
    public enum UserStatusEnum
    {
        OPT_IN = 1,
        OPT_OUT = 0,
        BLOCKED = 2,
        NO_OPTION_CHOSEN = 3,
        MANUALLY_ASSOCIATED_PATIENT = 4
    }
}
