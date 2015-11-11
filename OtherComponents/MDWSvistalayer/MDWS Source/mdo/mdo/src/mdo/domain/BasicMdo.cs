using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public abstract class BasicMdo
    {
        ISpringer springer;

        public ISpringer Springer
        {
            set { springer = value; }
        }

        public abstract string springMe();
    }
}
