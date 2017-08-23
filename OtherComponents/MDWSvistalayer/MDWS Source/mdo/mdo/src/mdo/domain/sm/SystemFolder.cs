using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.domain.sm
{
    [Serializable]
    public class SystemFolder : Folder
    {
        public SystemFolder() : base()
        {
            base.SystemFolder = true;
        }
    }
}
