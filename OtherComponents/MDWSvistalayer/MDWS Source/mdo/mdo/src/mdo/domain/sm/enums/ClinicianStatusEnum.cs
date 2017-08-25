using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.domain.sm.enums
{
    [Serializable]
    public enum ClinicianStatusEnum
    {
        INCOMPLETE = 0,
        INPROCESS = 10,
        COMPLETE = 20
    }
}
