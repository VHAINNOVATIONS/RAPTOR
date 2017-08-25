using System;
using System.Collections.Generic;
using System.Collections;
using System.Collections.Specialized;
using System.Text;
using gov.va.medora.mdo.dao;
using gov.va.medora.mdo.dao.vista;
using gov.va.medora.utils;
using gov.va.medora.mdo.exceptions;
using System.Reflection;
using gov.va.medora.mdo.src.mdo;

namespace gov.va.medora.mdo.dao.vista
{
    public class VistaPharmacyDao : IPharmacyDao
    {

        AbstractConnection cxn = null;

        public VistaPharmacyDao(AbstractConnection cxn)
        {
            this.cxn = cxn;
        }

        #region  RDV

        #region Outpatient Meds
        internal Medication[] getOutpatientMedsRdv()
        {
            return getOutpatientMedsRdv(cxn.Pid);
        }

        internal Medication[] getOutpatientMedsRdv(string dfn)
        {
            MdoQuery request = null;
            string response = "";
            try
            {
                request = buildGetOutpatientMedsRdvRequest(dfn);
                response = (string)cxn.query(request);
                return toOutpatientMedsRdv(response);
            }
            catch (Exception exc)
            {
                throw new MdoException(request, response, exc);
            }
        }

        internal MdoQuery buildGetOutpatientMedsRdvRequest(string dfn)
        {
            return VistaUtils.buildReportTextRequest_AllResults(dfn, "OR_RXOP:ALL OUTPATIENT~RXOP;ORDV06;28;");
        }

        internal Medication[] toOutpatientMedsRdv(string response)
        {
            int l = 0, f = 0 ;
            try
            {
                if (response == "")
                {
                    return null;
                }
                string[] lines = StringUtils.split(response, StringUtils.CRLF);
                ArrayList list = new ArrayList();
                Medication rec = null;
                string sig = "";
                for (int i = 0; i < lines.Length; i++)
                {
                    if (lines[i] == "")
                    {
                        continue;
                    }
                    string[] flds = StringUtils.split(lines[i], StringUtils.CARET);
                    int fldnum = Convert.ToInt32(flds[0]);
                    l = i;
                    switch (fldnum)
                    {
                        case 1:
                            if (rec != null)
                            {
                                rec.Sig = sig.TrimEnd();
                                rec.IsOutpatient = true;
                                rec.Type = "OP";
                                list.Add(rec);
                            }
                            rec = new Medication();
                            sig = "";
                            if (flds.Length == 2)
                            {
                                string[] parts = StringUtils.split(flds[1], StringUtils.SEMICOLON);
                                if (parts.Length == 2)
                                {
                                    rec.Facility = new SiteId(parts[1], parts[0]);
                                }
                                else if (flds[1] != "")
                                {
                                    rec.Facility = new SiteId(cxn.DataSource.SiteId.Id, flds[1]);
                                }
                                else
                                {
                                    rec.Facility = cxn.DataSource.SiteId;
                                }
                            }
                            break;
                        case 2:
                            if (flds.Length == 2)
                            {
                                rec.Name = flds[1];
                            }
                            break;
                        case 3:
                            if (flds.Length == 2)
                            {
                                rec.Drug = new KeyValuePair<string, string>(flds[1], rec.Name);
                                // now that we have the DRUG file IEN, we can determine if this item is a supply
                                rec.IsSupply = String.Equals("S", VistaUtils.getVariableValue(this.cxn, "$P($G(^PSDRUG(" + flds[1] + ",0)),U,3)"), StringComparison.CurrentCultureIgnoreCase);
                            }
                            break;
                        case 4:
                            if (flds.Length == 2)
                            {
                                rec.RxNumber = flds[1];
                            }
                            break;
                        case 5:
                            if (flds.Length == 2)
                            {
                                rec.Status = flds[1];
                            }
                            break;
                        case 6:
                            if (flds.Length == 2)
                            {
                                rec.Quantity = flds[1];
                            }
                            break;
                        case 7:
                            if (flds.Length == 2)
                            {
                                try {
                                    rec.ExpirationDate = VistaTimestamp.toUtcFromRdv(flds[1]);
                                } catch (Exception) {
                                    rec.ExpirationDate = "";
                                }
                            }
                            break;
                        case 8:
                            if (flds.Length == 2)
                            {
                                try {
                                    rec.IssueDate = VistaTimestamp.toUtcFromRdv(flds[1]);
                                } catch (Exception) {
                                    rec.IssueDate = "";
                                }
                            }
                            break;
                        case 9:
                            if (flds.Length == 2)
                            {
                                try {
                                    rec.LastFillDate = VistaTimestamp.toUtcFromRdv(flds[1]);
                                } catch (Exception) {
                                    rec.LastFillDate = "";
                                }
                            }
                            break;
                        case 10:
                            if (flds.Length == 2)
                            {
                                rec.Refills = flds[1];
                            }
                            break;
                        case 11:
                            if (flds.Length == 2)
                            {
                                rec.Provider = new Author("", flds[1], "");
                            }
                            break;
                        case 12:
                            if (flds.Length == 2)
                            {
                                rec.Cost = flds[1];
                            }
                            break;
                        case 14:
                            if (flds.Length == 2)
                            {
                                sig += flds[1] + '\n';
                            }
                            break;
                        case 15:
                            if (flds.Length == 2)
                            {
                                rec.Id = StringUtils.removeNonNumericChars(flds[1]);
                            }
                            break;
                        case 16:
                            if (flds.Length == 2)
                            {
                                try {
                                    rec.StopDate = VistaTimestamp.toUtcString(flds[1]);
                                } catch (Exception ex) {
                                    rec.ExpirationDate = "";
                                }
                            }
                            break;
                    }
                }
                if (rec != null)
                {
                    rec.Sig = sig.TrimEnd();
                    rec.IsOutpatient = true;
                    rec.Type = "OP";
                    list.Add(rec);
                }
                return (Medication[])list.ToArray(typeof(Medication));
            }
            catch (Exception ex)
            {
                throw ex;
            }
        }
        #endregion Outpatient Meds

        #region IV Meds
        public Medication[] getIvMedsRdv()
        {
            return getIvMedsRdv(cxn.Pid);
        }

        public Medication[] getIvMedsRdv(string dfn)
        {
            MdoQuery request = buildGetIvMedsRdvRequest(dfn);
            string response = (string)cxn.query(request);
            return toIvMedsRdv(response);
        }

        internal MdoQuery buildGetIvMedsRdvRequest(string dfn)
        {
            return VistaUtils.buildReportTextRequest_AllResults(dfn, "OR_IVA:ACTIVE IV~RXAV;ORDV06;0;");
        }

        internal Medication[] toIvMedsRdv(string response)
        {
            if (response == "")
            {
                return null;
            }
            string[] lines = StringUtils.split(response, StringUtils.CRLF);
            ArrayList list = new ArrayList();
            Medication rec = null;
            string additives = "";
            string solution = "";
            for (int i = 0; i < lines.Length; i++)
            {
                if (lines[i] == "")
                {
                    continue;
                }
                string[] flds = StringUtils.split(lines[i], StringUtils.CARET);
                int fldnum = Convert.ToInt32(flds[0]);
                switch (fldnum)
                {
                    case 1:
                        if (rec != null)
                        {
                            rec.Additives = additives;
                            rec.Solution = solution;
                            rec.IsInpatient = true;
                            rec.IsIV = true;
                            rec.Type = "IV";
                            list.Add(rec);
                        }
                        rec = new Medication();
                        additives = "";
                        solution = "";
                        if (flds.Length == 2)
                        {
                            string[] parts = StringUtils.split(flds[1], StringUtils.SEMICOLON);
                            if (parts.Length == 2)
                            {
                                rec.Facility = new SiteId(parts[1], parts[0]);
                            }
                            else if (flds[1] != "")
                            {
                                rec.Facility = new SiteId(cxn.DataSource.SiteId.Id, flds[1]);
                            }
                            else
                            {
                                rec.Facility = cxn.DataSource.SiteId;
                            }
                        }
                        break;
                    case 2:
                        if (flds.Length == 2)
                        {
                            additives += !String.IsNullOrEmpty(additives) ? "\n" : "";
                            additives += flds[1];

                            // we decided that "  " separates the dose from the drug
                            rec.Dose = flds[1].Substring(flds[1].LastIndexOf("  ") + 2);
                        }
                        break;
                    case 3:
                        if (flds.Length == 2)
                        {
                            solution += !String.IsNullOrEmpty(solution) ? "\n" : "";
                            solution += flds[1];
                        }
                        break;
                    case 4:
                        if (flds.Length == 2)
                        {
                            rec.Rate = flds[1];
                        }
                        break;
                    case 5:
                        if (flds.Length == 2)
                        {
                            rec.Schedule = flds[1];
                        }
                        break;
                    case 6:
                        if (flds.Length == 2)
                        {
                            rec.StartDate = VistaTimestamp.toUtcFromRdv(flds[1]);
                        }
                        break;
                    case 7:
                        if (flds.Length == 2)
                        {
                            rec.StopDate = VistaTimestamp.toUtcFromRdv(flds[1]);
                        }
                        break;
                }
            }
            if (rec != null)
            {
                rec.Additives = additives;
                rec.Solution = solution;
                rec.IsInpatient = true;
                rec.IsIV = true;
                rec.Type = "IV";
                list.Add(rec);
            }

            return (Medication[])list.ToArray(typeof(Medication));
        }
        #endregion IV Meds

        #region UnitDose Meds
        public Medication[] getUnitDoseMedsRdv()
        {
            return getUnitDoseMedsRdv(cxn.Pid);
        }

        public Medication[] getUnitDoseMedsRdv(string dfn)
        {
            MdoQuery request = null;
            string response = "";
            try
            {
                request = buildGetUnitDoseMedsRequestRdv(dfn);
                response = (string)cxn.query(request);
                return toUnitDoseMedsRdv(response);
            }
            catch (Exception exc)
            {
                throw new MdoException(request, response, exc);
            }
        }

        internal MdoQuery buildGetUnitDoseMedsRequestRdv(string dfn)
        {
            return VistaUtils.buildReportTextRequest_AllResults(dfn, "OR_RXUD:UNIT DOSE~RXUD;ORDV06;29;");
        }

        internal Medication[] toUnitDoseMedsRdv(string response)
        {
            if (response == "")
            {
                return null;
            }
            string[] lines = StringUtils.split(response, StringUtils.CRLF);
            ArrayList list = new ArrayList();
            Medication rec = null;
            string sig = "";
            for (int i = 0; i < lines.Length; i++)
            {
                if (lines[i] == "")
                {
                    continue;
                }
                string[] flds = StringUtils.split(lines[i], StringUtils.CARET);
                int fldnum = Convert.ToInt32(flds[0]);
                switch (fldnum)
                {
                    case 1:
                        if (rec != null)
                        {
                            rec.Drug = new KeyValuePair<string, string>(rec.Id, rec.Name);
                            rec.Sig = sig;
                            rec.IsInpatient = true;
                            rec.IsUnitDose = true;
                            rec.Type = "UD";
                            list.Add(rec);
                        }
                        rec = new Medication();
                        sig = "";
                        if (flds.Length == 2)
                        {
                            string[] parts = StringUtils.split(flds[1], StringUtils.SEMICOLON);
                            if (parts.Length == 2)
                            {
                                rec.Facility = new SiteId(parts[1], parts[0]);
                            }
                            else if (flds[1] != "")
                            {
                                rec.Facility = new SiteId(cxn.DataSource.SiteId.Id, flds[1]);
                            }
                            else
                            {
                                rec.Facility = cxn.DataSource.SiteId;
                            }
                        }
                        break;
                    case 2:
                        if (flds.Length == 2)
                        {
                            rec.Id = flds[1];
                        }
                        break;
                    case 3:
                        if (flds.Length == 2)
                        {
                            rec.Name = flds[1];
                        }
                        break;
                    case 4:
                        if (flds.Length == 2)
                        {
                            rec.Dose = flds[1];
                        }
                        break;
                    case 5:
                        if (flds.Length == 2)
                        {
                            rec.Status = flds[1];
                        }
                        break;
                    case 6:
                        if (flds.Length == 2)
                        {
                            rec.StartDate = VistaTimestamp.toUtcFromRdv(flds[1]);
                        }
                        break;
                    case 7:
                        if (flds.Length == 2)
                        {
                            rec.StopDate = VistaTimestamp.toUtcFromRdv(flds[1]);
                        }
                        break;
                    case 8:
                        if (flds.Length == 2)
                        {
                            rec.Route = flds[1];
                        }
                        break;
                    case 9:
                        if (flds.Length == 2)
                        {
                            rec.Schedule += flds[1];
                        }
                        break;
                }
            }
            if (rec != null)
            {
                rec.Drug = new KeyValuePair<string, string>(rec.Id, rec.Name);
                rec.Sig = sig;
                rec.IsInpatient = true;
                rec.IsUnitDose = true;
                rec.Type = "UD";
                list.Add(rec);
            }
            return (Medication[])list.ToArray(typeof(Medication));
        }
        #endregion UnitDose Meds

        #region Other Meds
        public Medication[] getOtherMedsRdv()
        {
            return getOtherMedsRdv(cxn.Pid);
        }

        public Medication[] getOtherMedsRdv(string dfn)
        {
            MdoQuery request = null;
            string response = "";
            try
            {
                request = buildGetOtherMedsRequestRdv(dfn);
                response = (string)cxn.query(request);
                return toOtherMedsRdv(response);
            }
            catch (Exception exc)
            {
                throw new MdoException(request, response, exc);
            }
        }

        internal MdoQuery buildGetOtherMedsRequestRdv(string dfn)
        {
            return VistaUtils.buildReportTextRequest_AllResults(dfn, "OR_RXN:HERBAL/OTC/NON-VA MEDS~NVA;ORDV06A;0;");
        }

        internal Medication[] toOtherMedsRdv(string response)
        {
            if (response == "")
            {
                return null;
            }
            string[] lines = StringUtils.split(response, StringUtils.CRLF);
            ArrayList list = new ArrayList();
            Medication rec = null;
            string sig = "";
            string comment = "";
            for (int i = 0; i < lines.Length; i++)
            {
                if (lines[i] == "")
                {
                    continue;
                }
                string[] flds = StringUtils.split(lines[i], StringUtils.CARET);
                int fldnum = Convert.ToInt32(flds[0]);
                switch (fldnum)
                {
                    case 1:
                        if (rec != null)
                        {
                            rec.Comment = comment;
                            rec.Sig = sig;
                            rec.Type = "NV";
                            list.Add(rec);
                        }
                        rec = new Medication();
                        rec.IsOutpatient = true;
                        rec.IsNonVA = true;
                        sig = "";
                        comment = "";
                        if (flds.Length == 2)
                        {
                            string[] parts = StringUtils.split(flds[1], StringUtils.SEMICOLON);
                            if (parts.Length == 2)
                            {
                                rec.Facility = new SiteId(parts[1], parts[0]);
                            }
                            else if (flds[1] != "")
                            {
                                rec.Facility = new SiteId(cxn.DataSource.SiteId.Id, flds[1]);
                            }
                            else
                            {
                                rec.Facility = cxn.DataSource.SiteId;
                            }
                        }
                        break;
                    case 2:
                        if (flds.Length == 2)
                        {
                            rec.Name = flds[1];
                        }
                        break;
                    case 3:
                        if (flds.Length == 2)
                        {
                            rec.Status = flds[1];
                        }
                        break;
                    case 4:
                        if (flds.Length == 2)
                        {
                            rec.StartDate = VistaTimestamp.toUtcFromRdv(flds[1]);
                        }
                        break;
                    case 5:
                        if (flds.Length == 2)
                        {
                            rec.DateDocumented = VistaTimestamp.toUtcFromRdv(flds[1]);
                        }
                        break;
                    case 6:
                        if (flds.Length == 2)
                        {
                            rec.Documentor = new Author("", flds[1], "");
                        }
                        break;
                    case 7:
                        if (flds.Length == 2)
                        {
                            rec.StopDate = VistaTimestamp.toUtcFromRdv(flds[1]);
                        }
                        break;
                    case 8:
                        if (flds.Length == 2)
                        {
                            sig += !String.IsNullOrEmpty(sig) ? "\n" : "";
                            sig += flds[1];
                        }
                        break;
                    case 10:
                        if (flds.Length == 2)
                        {
                            comment += !String.IsNullOrEmpty(comment) ? "\n" : "";
                            comment += flds[1];
                        }
                        break;
                }
            }
            if (rec != null)
            {
                rec.Comment = comment;
                rec.Sig = sig;
                rec.Type = "NV";
                list.Add(rec);
            }
            return (Medication[])list.ToArray(typeof(Medication));
        }
        #endregion Other Meds

        #endregion RDV

        public Medication[] getVaMeds()
        {
            return getVaMeds(cxn.Pid);
        }

        public Medication[] getVaMeds(string dfn)
        {
            MdoQuery request = null;
            string response = "";

            try
            {
                request = buildGetVaMedsRequest(dfn);
                response = (string)cxn.query(request);
                return toVaMeds(response);
            }
            catch (Exception exc)
            {
                throw new MdoException(request, response, exc);
            }
        }

        internal MdoQuery buildGetVaMedsRequest(string dfn)
        {
            VistaUtils.CheckRpcParams(dfn);

            VistaQuery vq = new VistaQuery("ORWPS COVER");
            vq.addParameter(vq.LITERAL, dfn);
            return vq;
        }

        internal Medication[] toVaMeds(string response)
        {
            if (response == "")
            {
                return null;
            }
            string[] rex = StringUtils.split(response, StringUtils.CRLF);
            ArrayList list = new ArrayList();
            for (int i = 0; i < rex.Length; i++)
            {
                if (rex[i] == "" || rex[i][0] == '^' || rex[i].IndexOf("^??^") > 0)
                {
                    continue;
                }
                Medication med = new Medication();
                string[] flds = StringUtils.split(rex[i], StringUtils.CARET);
                med.Facility = cxn.DataSource.SiteId;
                string[] parts = StringUtils.split(flds[0], StringUtils.SEMICOLON);
                med.Id = flds[0];
                med.IsInpatient = (parts[1] == "I");
                med.IsOutpatient = (parts[1] == "O");
                med.Name = flds[1];
                if (parts[0].EndsWith("V"))
                {
                    med.IsIV = true;
                }
                else if (parts[0].EndsWith("U"))
                {
                    med.IsUnitDose = true;
                }
                else if (parts[0].EndsWith("N"))
                {
                    med.IsNonVA = true;
                }
                med.OrderId = flds[2];
                med.Status = flds[3];
                list.Add(med);
            }
            return (Medication[])list.ToArray(typeof(Medication));
        }
        
        public string getMedicationDetail(string ien)
        {
            return getMedicationDetail(cxn.Pid, ien);
        }

        public string getMedicationDetail(string dfn, string ien)
        {
            // NOTE: this rpc doesn't care one way or the other what the dfn is.
            MdoQuery request = null;
            string response = "";
            try
            {
                request = buildGetMedicationDetail(dfn, ien);
                response = (string)cxn.query(request);
                return response;
            }
            catch (Exception exc)
            {
                throw new MdoException(request, response, exc);
            }
        }

        internal MdoQuery buildGetMedicationDetail(string dfn, string ien)
        {
            // NOTE: this rpc doesn't care one way or the other what the dfn is.
            VistaQuery vq = new VistaQuery("ORWPS DETAIL");
            vq.addParameter(vq.LITERAL, dfn);
            vq.addParameter(vq.LITERAL, ien);
            return vq;
        }

        public string getOutpatientRxProfile()
        {
            return getOutpatientRxProfile(cxn.Pid);
        }

        public string getOutpatientRxProfile(string dfn)
        {
            MdoQuery request = null;
            string response = "";

            try
            {
                request = buildGetOutpatientRxProfileRequest(dfn);
                response = (string)cxn.query(request);
                return response;
            }
            catch (Exception exc)
            {
                throw new MdoException(request, response, exc);
            }
        }

        internal MdoQuery buildGetOutpatientRxProfileRequest(string dfn)
        {
            return VistaUtils.buildReportTextRequest_AllResults(dfn, "13:OUTPATIENT RX PROFILE~;;0");
        }

        public string getMedsAdminHx(string fromDate, string toDate, int nrpts)
        {
            return getMedsAdminHx(cxn.Pid, fromDate, toDate, nrpts);
        }

        public string getMedsAdminHx(string dfn, string fromDate, string toDate, int nrpts)
        {
            MdoQuery request = null;
            string response = "";
            try
            {
                request = buildGetMedsAdminHxRequest(dfn, fromDate, toDate, nrpts);
                response = (string)cxn.query(request);
                return response;
            }
            catch (Exception exc)
            {
                throw new MdoException(request, response, exc);
            }
        }

        internal MdoQuery buildGetMedsAdminHxRequest(string dfn, string fromDate, string toDate, int nrpts)
        {
            return VistaUtils.buildReportTextRequest(dfn, fromDate, toDate, nrpts, "22:MED ADMIN HISTORY (BCMA)~;;0;");
        }

        public string getMedsAdminLog(string fromDate, string toDate, int nrpts)
        {
            return getMedsAdminLog(cxn.Pid, fromDate, toDate, nrpts);
        }

        public string getMedsAdminLog(string dfn, string fromDate, string toDate, int nrpts)
        {
            MdoQuery request = null;
            string response = "";
            try
            {
                request = buildGetMedsAdminLogRequest(dfn, fromDate, toDate, nrpts);
                response = (string)cxn.query(request);
                return response;
            }
            catch (Exception exc)
            {
                throw new MdoException(request, response, exc);
            }
        }

        internal MdoQuery buildGetMedsAdminLogRequest(string dfn, string fromDate, string toDate, int nrpts)
        {
            //23:MED ADMIN LOG (BCMA)~;;0;100
            return VistaUtils.buildReportTextRequest(dfn, fromDate, toDate, nrpts, "23:MED ADMIN LOG (BCMA)~;;0;");
        }

        public string getImmunizations(string fromDate, string toDate, int nrpts)
        {
            return getImmunizations(cxn.Pid, fromDate, toDate, nrpts);
        }

        public string getImmunizations(string dfn, string fromDate, string toDate, int nrpts)
        {
            MdoQuery request = null;
            string response = "";
            try
            {
                request = buildGetImmunizationsRequest(dfn, fromDate, toDate, nrpts);
                response = (string)cxn.query(request);
                return response;
            }
            catch (Exception exc)
            {
                throw new MdoException(request, response, exc);
            }
        }

        public MdoQuery buildGetImmunizationsRequest(string dfn, string fromDate, string toDate, int nrpts)
        {
            return VistaUtils.buildReportTextRequest(dfn, fromDate, toDate, nrpts, "OR_IM:IMMUNIZATIONS~;;207;");
        }

        public string discontinueMed(string orderIen, string duz, string reasonIen)
        {
            if (String.IsNullOrEmpty(orderIen))
            {
                return "No order ID";
            }
            if (String.IsNullOrEmpty(duz))
            {
                return "No user ID";
            }
            if (String.IsNullOrEmpty(reasonIen))
            {
                return "No reason ID";
            }

            VistaUserDao userDao = new VistaUserDao(cxn);
            if (!userDao.hasPermission(duz, new SecurityKey("", "PROVIDER")))
            {
                return "User does not have PROVIDER key";
            }

            VistaOrdersDao orderDao = new VistaOrdersDao(cxn);
            Order order = orderDao.getOrder(orderIen);
            if (order == null)
            {
                return "No such order";
            }

            string msg = orderDao.validateOrderActionNature(orderIen, "DC", duz, "");
            if (msg != "OK")
            {
                return msg;
            }
            msg = orderDao.getComplexOrderMsg(orderIen);
            if (msg != "")
            {
                return msg;
            }

            if (!orderDao.lockOrdersForPatient(cxn.Pid))
            {
                return "Unable to lock orders for patient";
            }
            msg = orderDao.lockOrder(orderIen);
            if (msg != "OK")
            {
                orderDao.unlockOrdersForPatient();
                return msg;
            }

            // discontinue the order

            // unlock ?

            return null;
        }

        #region get medication mdos

        internal MdoQuery buildGetMedsTabRequest(string dfn, string duz)
        {
            VistaUtils.CheckRpcParams(dfn);

            //if (!VistaUtils.isWellFormedDuz(duz))
            //{
            //    throw new MdoException(MdoExceptionCode.ARGUMENT_INVALID_NUMERIC_REQUIRED, "Invalidly formed duz: " + duz);
            //}

            VistaQuery vq = new VistaQuery("ORWPS ACTIVE");
            vq.addParameter(vq.LITERAL, dfn);
            //vq.addParameter(vq.LITERAL, duz);
            //vq.addParameter(vq.LITERAL, "0");
            //vq.addParameter(vq.LITERAL, "1");
            return vq;
        }

        public Medication[] getAllMeds()
        {
            return getAllMeds(cxn.Pid);
        }

        public Medication[] getAllMeds(string dfn)
        {
            return getAllMeds(dfn, cxn.Uid);
        }

        public Medication[] getAllMeds(string dfn, string duz)
        {
            return getMedsWithFilter(dfn, duz);            
        }

        public Medication[] getOutpatientMeds()
        {
            return getOutpatientMeds(cxn.Pid);
        }

        public Medication[] getOutpatientMeds(string dfn)
        {
            return getOutpatientMeds(dfn, cxn.Uid);
        }

        public Medication[] getOutpatientMeds(string dfn, string duz)
        {
            return getMedsWithFilter(dfn, duz, Medication.MedicationType.OUTPATIENT);
        }

        public Medication[] getIvMeds()
        {
            return getIvMeds(cxn.Pid);
        }

        public Medication[] getIvMeds(string dfn)
        {
            return getIvMeds(dfn, cxn.Uid);
        }

        public Medication[] getIvMeds(string dfn, string duz)
        {
            return getMedsWithFilter(dfn, duz, Medication.MedicationType.IV);
        }

        public Medication[] getUnitDoseMeds()
        {
            return getUnitDoseMeds(cxn.Pid);
        }

        public Medication[] getUnitDoseMeds(string dfn)
        {
            return getUnitDoseMeds(dfn, cxn.Uid);
        }

        public Medication[] getUnitDoseMeds(string dfn, string duz)
        {
            return getMedsWithFilter(dfn, duz, Medication.MedicationType.UNITDOSE);
        }

        public Medication[] getOtherMeds()
        {
            return getOtherMeds(cxn.Pid);
        }

        public Medication[] getOtherMeds(string dfn)
        {
            return getOtherMeds(dfn, cxn.Uid);
        }

        public Medication[] getOtherMeds(string dfn, string duz)
        {
            return getMedsWithFilter(dfn, duz, Medication.MedicationType.NONVA);
        }

        public Medication[] getInpatientForOutpatientMeds()
        {
            return getInpatientForOutpatientMeds(cxn.Pid);
        }

        public Medication[] getInpatientForOutpatientMeds(string pid)
        {
            return getInpatientForOutpatientMeds(pid, cxn.Uid);
        }

        public Medication[] getInpatientForOutpatientMeds(string pid, string duz)
        {
            return getMedsWithFilter(pid, duz, Medication.MedicationType.INFOROUT);
        }

        //public Medication[] getInpatientForOutpatientMeds()
        //{
        //    return getInpatientForOutpatientMeds(cxn.Pid);
        //}

        //public Medication[] getInpatientForOutpatientMeds(string dfn)
        //{
        //   return getInpatientForOutpatientMeds(dfn, cxn.Uid);
        //}

        //public Medication[] getInpatientForOutpatientMeds(string dfn, string duz)
        //{
        //    return getMedsWithFilter(dfn, duz, Medication.MedicationType.INFOROUT);
        //}

        /// <summary>
        /// Get a list of medications for the patient filtered by medication type
        ///     
        /// </summary>
        /// <param name="dfn"></param>
        /// <param name="duz"></param>
        /// <param name="filter"></param>
        /// <returns></returns>
        public Medication[] getMedsWithFilter(string dfn, string duz, params Medication.MedicationType[] filter)
        {
            MdoQuery request = null;
            string response = "";

            try
            {
                request = buildGetMedsTabRequest(dfn, duz);
                response = (string)cxn.query(request);

                HashSet<Medication.MedicationType> filterSet = new HashSet<Medication.MedicationType>(filter);

                Medication[] meds = toMedsFromTab(dfn, response, filterSet);

                // **** Hack Alert ****
                // Since it is impossible to reliably match other meds in the tab call to the rdv call,
                // we're just going to give the RDV results as those results have most of what we need,
                // ideally we would like to be consitent with med.Drug and med.Id, but the fields needed to 
                // construct them aren't available in the RDV
                if(filterSet.Count == 0 || filterSet.Contains(Medication.MedicationType.NONVA)) 
                {
                    List<Medication> temp = new List<Medication>(meds);
                    Medication[] other = getOtherMedsRdv(dfn);
                    if (null != other)
                    {
                        temp.AddRange(other);
                        meds = temp.ToArray();
                    }
                }

                return meds.Length > 0 ? meds : null;
            }
            catch (Exception exc)
            {
                if (exc is MdoException)
                {
                    throw exc;
                }

                throw new MdoException(request, response, exc);
            }
        }

        /// <summary>
        /// Construct a list of requested medications base on the filter,  it the filter is empty all meds will be returned
        ///     * note at this time, since it is impossible to match other meds from the tab call
        ///       to the rdv results, those meds will not be returned
        /// </summary>
        /// <param name="dfn">patient identifier</param>
        /// <param name="response">med tab RPC response to parse</param>
        /// <param name="filter">set of Medication types to return</param>
        /// <returns>list of filtered medications from meds tab supplimented with rdv data</returns>
        internal Medication[] toMedsFromTab(string dfn, string response, HashSet<Medication.MedicationType> filter)
        {
            if (String.IsNullOrEmpty(response))
            {
                return new Medication[0];
            }
            
            // if there are no filters defined let's assume the caller wants all meds
            if (null == filter || filter.Count == 0) 
            {
                filter = new HashSet<Medication.MedicationType>(Medication.MedicationType.Values);
            }

            // the final list
            Dictionary<string, Medication> results = new Dictionary<string, Medication>();

            // each record is delimited by ~
            List<string> rawMeds = new List<string>(StringUtils.split(response, StringUtils.TILDE));

            Dictionary<string, Medication> opSupplement = null;
            Medication[] unitDoseSupplement = null;

            foreach (string rawMed in rawMeds)
            {
                // the first entry will typically be empty due to the split on TILDE, however
                // the first entry may be a integer indicating display instructions to CPRS which we can ignore

                if (!String.IsNullOrEmpty(rawMed) && !StringUtils.isNumeric(rawMed.Trim()))
                {
                    string delimitedFields = rawMed.Substring(0, rawMed.IndexOf(StringUtils.CRLF));
                    string textBlob = rawMed.Substring(rawMed.IndexOf(StringUtils.CRLF) + StringUtils.CRLF.Length);

                    //          1     2      3     4       5     6       7       8        9      10     11
                    // Pieces: Typ^PharmID^Drug^InfRate^StopDt^RefRem^TotDose^UnitDose^OrderID^Status^LastFill
                    string[] fields = StringUtils.split(delimitedFields, StringUtils.CARET);
                    Medication.MedicationType type = Medication.MedicationType.valueOf(StringUtils.split(fields[0], ":")[0]);

                    Medication med = null;
                    if (type == Medication.MedicationType.OUTPATIENT && filter.Contains(Medication.MedicationType.OUTPATIENT))
                    {
                        if (null == opSupplement) // lazy load the out patient meds from rdv for supplement
                        {
                            opSupplement = medsAsDictionary(getOutpatientMedsRdv(dfn));
                        }

                        med = toOutpatientMed(fields, textBlob, opSupplement);
                    }
                    else if (type == Medication.MedicationType.IV && filter.Contains(Medication.MedicationType.IV))
                    {
                        med = toIvMed(dfn, fields, textBlob);
                    }
                    else if (type == Medication.MedicationType.UNITDOSE && filter.Contains(Medication.MedicationType.UNITDOSE))
                    {
                        if (null == unitDoseSupplement)
                        {
                            unitDoseSupplement = getUnitDoseMedsRdv(dfn);
                        }

                        med = toUnitDoseMed(dfn, fields, textBlob, unitDoseSupplement);
                    }
                    else if (type == Medication.MedicationType.INFOROUT && filter.Contains(Medication.MedicationType.INFOROUT))
                    {
                        med = toInpatientForOutpatientMed(dfn, fields, textBlob);
                    }
                    else
                    {
                        // would be nice to add Other Meds here, but there is not enough info to match
                        // tabs to rdv, since most of the data needed is in the RDV the call to getAllMeds will append
                        // the getOtherMedsRdv results and the getOtherMeds call will just return getOtherMedsRdv
                    }

                    if (med != null && !results.ContainsKey(med.Id))
                    {
                        results.Add(med.Id, med);
                    }
                }
            }

            List<Medication> temp = new List<Medication>(results.Values);
            return temp.ToArray();
        }

        /// <summary>
        /// Construct an Outpatient Medication MDO from CPRS Meds Tab supplimented with RDV
        /// </summary>
        /// <param name="dfn">Patient identifier used for Medication.Id</param>
        /// <param name="fields">CPRS MedTab fields</param>
        /// <param name="textBlob">Text blob details from CPRS MedTab</param>
        /// <param name="rdvSupplement">Dictionary of OP Medication from RDV call</param>
        /// <returns>OP Medication MDO if supplement is available, null otherwise</returns>
        internal Medication toOutpatientMed(string[] fields, string textBlob, Dictionary<string, Medication> rdvSupplement)
        {
            Medication med = new Medication();
            med.Type = Medication.MedicationType.OUTPATIENT.Code;
            med.Id = fields[1];
            med.Facility = cxn.DataSource.SiteId;
            med.Name = fields[2];
            med.Refills = fields[5];
            med.OrderId = fields[8];
            med.Status = fields[9];
            med.LastFillDate = VistaTimestamp.toUtcString(fields[10]);
            med.DaysSupply = fields[11];
            med.Quantity = fields[12];
            med.IsOutpatient = true;

            string[] textFields = StringUtils.split(textBlob, StringUtils.CRLF);
            if (textFields.Length > 0)
            {
                int index = 0;
                while (!textFields[index].StartsWith("\\"))
                {
                    med.Detail += textFields[index++] + StringUtils.CRLF;
                }

                while (index < textFields.Length && textFields[index] != "")
                {
                    med.Sig += textFields[index++];
                }

                med.Detail = med.Detail.Trim();
                med.Sig = med.Sig.Trim();
            }

            // add the rdv supplemented fields
            // the med id from the rdv call is numeric only, so we have to remove the R;O to find it
            string numericMedId = StringUtils.removeNonNumericChars(med.Id);
            if (rdvSupplement != null && rdvSupplement.ContainsKey(numericMedId))
            {
                Medication supplement = rdvSupplement[numericMedId];
                med.RxNumber = supplement.RxNumber;
                med.Drug = supplement.Drug;
                med.Cost = supplement.Cost;
                med.StopDate = supplement.StopDate;
                med.ExpirationDate = supplement.ExpirationDate;
                med.IssueDate = supplement.IssueDate;
                med.Provider = supplement.Provider;
                med.Sig = supplement.Sig;
                med.IsSupply = supplement.IsSupply;
            }

            return med;
        }

        /// <summary>
        /// Construct an IV Medication MDO from CPRS Tab
        /// </summary>
        /// <param name="dfn">Patient identifier used for Medication.Id</param>
        /// <param name="fields">CPRS MedTab fields</param>
        /// <param name="textBlob">Text blob details from CPRS MedTab</param>
        /// <returns>IV Medication MDO</returns>
        internal Medication toIvMed(string dfn, string[] fields, string textBlob)
        {
            Medication med = new Medication();
            med.Type = Medication.MedicationType.IV.Code;
            med.Id = fields[1];
            med.Name = fields[2];
            med.OrderId = fields[8];
            med.Facility = cxn.DataSource.SiteId;
            med.Route = "INTRAVENOUS";
            med.Dose = fields[6];
            med.IsIV = true;
            med.IsInpatient = true;
            med.Status = fields[9];
            med.StopDate = VistaTimestamp.toUtcString(fields[4]);
            med.StartDate = VistaTimestamp.toUtcString(fields[15]);

            // this is the temporary solution needed for The Daily Plan
            med.Detail = textBlob;

            string[] textFields = StringUtils.split(textBlob, StringUtils.CRLF);
            
            if (textFields.Length > 0)
            {
                // temporarily make the following assumptions until new RPCs are available for The Daily Plan
                // Line 1:  Additive  +  Dose
                // Line Containing "\in" is the solution
                // Line Containing "\IV" is the Schedule
                // Everything else we don't understand... but its in the detail field if needed
                if (String.IsNullOrEmpty(med.Dose))
                {
                    int split = StringUtils.firstIndexOfNum(textFields[0]);
                    med.Dose = textFields[0].Substring(split);
                    med.Additives = textFields[0].Substring(0, split).Trim();
                }

                foreach (String line in textFields)
                {
                    if (line.Contains(@"\in"))
                    {
                        med.Solution = line.Replace(@"\in", "").Trim();
                    }
                    else if (line.Contains(@"\IV "))
                    {
                        med.Schedule = line.Replace(@"\IV", "").Trim();
                    }
                }
            }

            return med;
        }
        
        /// <summary>
        /// Attempts to match a unit dose medication from the tab call to one from the rdv call
        /// </summary>
        /// <param name="ud">Unit dose from Tab call</param>
        /// <param name="list">List of Unit Dose Meds from RDV</param>
        /// <returns>Medication from RDV list if matched, null otherwise</returns>
        internal Medication matchUnitDose(Medication ud, Medication[] list)
        {
            foreach (Medication match in list)
            {
                if (ud.Drug.Value.Equals(match.Drug.Value)
                    && ud.StartDate.Equals(match.StartDate)
                        && ud.StopDate.Equals(match.StopDate) 
                            && ud.Dose.Equals(match.Dose)) 
                {
                    return match;
                }
            }
            
            return null;
        }

        /// <summary>
        /// Construct a UnitDose Medication MDO from the CPRS Tab fields supplemented with RDV
        /// </summary>
        /// <param name="dfn">Patient identifier used for Medication.Id</param>
        /// <param name="fields">CPRS MedTab fields</param>
        /// <param name="textBlob">Text blob details from CPRS MedTab</param>
        /// <param name="rdvSupplement">List of UD Medications from RDV call for supplement</param>
        /// <returns>UD Medication MDO</returns>
        internal Medication toUnitDoseMed(string dfn, string[] fields, string textBlob, Medication[] rdvSupplement)
        {
            Medication med = new Medication();
            med.Type = Medication.MedicationType.UNITDOSE.Code;
            med.Id = fields[1];
            med.Facility = cxn.DataSource.SiteId;
            med.OrderId = fields[8];
            med.IsUnitDose = true;
            med.IsInpatient = true;
            med.Status = fields[9];

            // need to add dose/start date/stop date for rdv supplement lookup
            // create a dummy Drug entry as to match with RDV
            med.Drug = new KeyValuePair<string, string>("", fields[2]);
            med.Name = fields[2];
            med.Dose = fields[6];
            med.StartDate = VistaTimestamp.toUtcString(fields[15]);
            med.StopDate = VistaTimestamp.toUtcString(fields[4]);

            med.Detail = textBlob;

            // add the rdv supplemented fields
            Medication supplement = matchUnitDose(med, rdvSupplement);
            if (null != supplement)
            {
                med.Drug = supplement.Drug;
                med.StopDate = supplement.StopDate;
                med.StartDate = supplement.StartDate;
                med.Route = supplement.Route;
                med.Dose = supplement.Dose;
                med.Schedule = supplement.Schedule;
                med.Status = supplement.Status;
            }

            return med;
        }

        /// <summary>
        /// Construct a Inpatient For Outpatient Medication MDO from the CPRS Tab fields
        /// </summary>
        /// <param name="dfn">Patient identifier used for Medication.Id</param>
        /// <param name="fields">CPRS MedTab fields</param>
        /// <param name="textBlob">Text blob details from CPRS MedTab</param>
        /// <returns>CP Medication MDO </returns>
        internal Medication toInpatientForOutpatientMed(string dfn, string[] fields, string textBlob)
        {
            Medication med = new Medication();
            med.Type = Medication.MedicationType.INFOROUT.Code;
            med.Id = fields[1];
            med.Facility = cxn.DataSource.SiteId;
            med.OrderId = fields[8];
            med.IsOutpatient = true;
            med.IsImo = true;

            string[] hospital = StringUtils.split(fields[0], ":");
            med.Hospital = new KeyValuePair<string, string>(hospital[2], hospital[1]);

            med.Drug = new KeyValuePair<string, string>("", fields[2]);
            med.Name = fields[2];
            med.Dose = fields[6];
            med.StartDate = VistaTimestamp.toUtcString(fields[15]);
            med.StopDate = VistaTimestamp.toUtcString(fields[4]);

            med.Detail = textBlob;

            return med;
        }
        /// <summary>
        /// returns a dictionary containing the medications indexed by Id
        /// </summary>
        /// <param name="meds">List of medications to dictionaryify</param>
        /// <returns>dictionary</returns>
        internal Dictionary<string, Medication> medsAsDictionary(Medication[] meds)
        {
            Dictionary<string, Medication> medsDict = new Dictionary<string, Medication>();
            if (null != meds && meds.Length > 0)
            {
                foreach (Medication med in meds)
                {
                    if (!medsDict.ContainsKey(med.Id))
                    {
                        medsDict.Add(med.Id, med);
                    }
                }
            }

            return medsDict;
        }

        #endregion get medication mdos


        public Medication refillPrescription(string rxId)
        {
            throw new NotImplementedException();
        }
    }
}
