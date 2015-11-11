using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.domain.sm.enums
{
    [Serializable]
    public enum AddresseeRoleEnum
    {
        SENDER,
        RECIPIENT,
        CC,
        BCC
    }
}
