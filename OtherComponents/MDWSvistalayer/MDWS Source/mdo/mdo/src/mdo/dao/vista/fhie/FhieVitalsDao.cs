using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo.exceptions;
using gov.va.medora.mdo.src.mdo;

namespace gov.va.medora.mdo.dao.vista.fhie
{
    public class FhieVitalsDao : IVitalsDao
    {
        AbstractConnection cxn = null;
        VistaVitalsDao vistaDao = null;

        public FhieVitalsDao(AbstractConnection cxn)
        {
            this.cxn = cxn;
            vistaDao = new VistaVitalsDao(cxn);
        }

        public VitalSignSet[] getVitalSigns()
        {
            return getVitalSigns(cxn.Pid);
        }

        public VitalSignSet[] getVitalSigns(string dfn)
        {
            MdoQuery mq = buildGetVitalSignsRequest(dfn);
            string response = (string)cxn.query(mq);
            return vistaDao.toVitalSignsFromRdv(response);
        }

        internal MdoQuery buildGetVitalSignsRequest(string dfn)
        {
            return VistaUtils.buildReportTextRequest_AllResults(dfn, "OR_DODVS:VITAL SIGNS~VS;ORDV04;47;");
        }

        public VitalSignSet[] getVitalSigns(string fromDate, string toDate, int maxRex)
        {
            return getVitalSigns(cxn.Pid, fromDate,toDate, maxRex);
        }

        public VitalSignSet[] getVitalSigns(string dfn, string fromDate, string toDate, int maxRex)
        {
            string request = buildGetVitalSignsRequest(dfn, fromDate, toDate, maxRex);
            string response = (string)cxn.query(request);
            return vistaDao.toVitalSignsFromRdv(response);
        }

        internal string buildGetVitalSignsRequest(string dfn, string fromDate, string toDate, int maxRex)
        {
            return VistaUtils.buildReportTextRequest(dfn, fromDate, toDate, maxRex, "OR_DODVS:VITAL SIGNS~VS;ORDV04;47;").buildMessage();
        }

        public VitalSign[] getLatestVitalSigns()
        {
            return getLatestVitalSigns(cxn.Pid);
        }

        // TBD: Build latest from getVitalSigns?
        public VitalSign[] getLatestVitalSigns(string pid)
        {
            if (!VistaUtils.isWellFormedIen(pid))
            {
                throw new MdoException(MdoExceptionCode.ARGUMENT_INVALID, "Invalid DFN: ");
            }

            return null;
        }
    }
}
