using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;
using gov.va.medora.utils;
using gov.va.medora.mdo.exceptions;
using gov.va.medora.mdo.src.mdo;

namespace gov.va.medora.mdo.dao.vista
{
    public class VistaConsultDao : IConsultDao
    {
        AbstractConnection  cxn = null;

        public VistaConsultDao(AbstractConnection cxn)
        {
            this.cxn = cxn;
        }

        public Consult[] getConsultsForPatient()
        {
            return getConsultsForPatient(cxn.Pid);
        }

        public Consult[] getConsultsForPatient(string dfn)
        {
            MdoQuery request = buildGetConsultsForPatientRequest(dfn);
            string response = (string)cxn.query(request);
            return toConsults(response);
        }

        internal MdoQuery buildGetConsultsForPatientRequest(string dfn)
        {
            VistaUtils.CheckRpcParams(dfn);
            VistaQuery vq = new VistaQuery("ORQQCN LIST");
            vq.addParameter(vq.LITERAL, dfn);
            return vq;
        }

        internal Consult[] toConsults(string response)
        {
            if (String.IsNullOrEmpty(response) || response.StartsWith("< PATIENT DOES NOT HAVE ANY CONSULTS/REQUESTS"))
            {
                return null;
            }
            string[] rex = StringUtils.split(response, StringUtils.CRLF);
            rex = StringUtils.trimArray(rex);
            Consult[] result = new Consult[rex.Length];
            for (int i = 0; i < rex.Length; i++)
            {
                string[] flds = StringUtils.split(rex[i], StringUtils.CARET);
                Consult c = new Consult();
                c.Id = flds[0];
                c.Text = getConsultNote(c.Id);
                c.Timestamp = VistaTimestamp.toDateTime(flds[1]);
                c.Status = VistaOrdersDao.decodeOrderStatus(flds[2]);
                c.Title = flds[6];
                //c.Service = new KeyValuePair<string, string>("", flds[2]);
                result[i] = c;
            }
            return result;
        }

        public string getOrderNumberForConsult(string consultIen)
        {
            MdoQuery request = buildGetOrderNumberForConsultRequest(consultIen);
            string response = (string)cxn.query(request);
            return response;
        }

        internal MdoQuery buildGetOrderNumberForConsultRequest(string consultIen)
        {
            VistaUtils.CheckRpcParams(consultIen);
            VistaQuery vq = new VistaQuery("ORQQCN GET ORDER NUMBER");
            vq.addParameter(vq.LITERAL, consultIen);
            return vq;
        }

        public string getConsultNote(string consultIen)
        {
            MdoQuery request = buildGetConsultNoteRequest(consultIen);
            string response = (string)cxn.query(request);
            // fix for #2718
            //return response;
            return StringUtils.stripInvalidXmlCharacters(response);
        }

        internal MdoQuery buildGetConsultNoteRequest(string consultIen)
        {
            VistaUtils.CheckRpcParams(consultIen);
            VistaQuery vq = new VistaQuery("ORQQCN DETAIL");
            vq.addParameter(vq.LITERAL, consultIen);
            return vq;
        }
    }
}
