using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Collections;

namespace gov.va.medora.mdo
{
    public abstract class MdoQuery
    {
        public abstract string buildMessage();
        public abstract ArrayList Parameters
        {
            get;
            set;
        }

        public abstract String RpcName
        {
            get;
            set;
        }
    }
}
