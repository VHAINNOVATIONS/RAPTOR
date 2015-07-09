using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.domain.sm.enums
{
    // These types came from SM database
    [Serializable]
    public enum UserTypeEnum
    {
        PATIENT = 0,
        CLINICIAN = 1,
        ADMINISTRATOR = 2
    }
}
