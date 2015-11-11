using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.dao.soap.rdw
{
    public class RdwCredentials : AbstractCredentials
    {
        public RdwCredentials() : base() { }

        public override bool AreTest
        {
            get { return false; }
        }

        public override bool Complete
        {
            get { return true; }
        }
    }
}
