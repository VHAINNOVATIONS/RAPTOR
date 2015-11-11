using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.dao.vista
{
    public class VistaOrdersDao2
    {
        VistaConnection _cxn;

        public VistaOrdersDao2(AbstractConnection cxn)
        {
            _cxn = cxn as VistaConnection;
        }

        public bool getSignedOnChartParameter()
        {
            return String.Equals("1", getParameterValue("OR SIGNED ON CHART"));
        }

        public String getParameterValue(String arg)
        {
            MdoQuery request = buildGetParameterValueRequest(arg);
            String response = (String)_cxn.query(request);
            return response;
        }

        internal MdoQuery buildGetParameterValueRequest(String arg)
        {
            VistaQuery vq = new VistaQuery("ORWU PARAM");
            vq.addParameter(vq.LITERAL, arg);
            return vq;
        }

    }
}
