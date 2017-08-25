using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.dao.sql.cdw
{
    public class CdwCredentials : AbstractCredentials
    {
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
