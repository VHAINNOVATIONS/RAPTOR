using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo.dao.vista
{
    public class VistaSystemDao : SystemDao
    {
        VistaConnection cxn = null;

        public VistaSystemDao(Connection cxn)
        {
            this.cxn = (VistaConnection)cxn;
        }

        public DateTime getTimestamp()
        {
            return cxn.getTimestamp();
        }

    }
}
