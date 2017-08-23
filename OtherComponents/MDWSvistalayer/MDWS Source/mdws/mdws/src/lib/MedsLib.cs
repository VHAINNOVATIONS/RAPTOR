using System;
using System.Web;
using System.Collections.Specialized;
using System.Collections.Generic;
using System.Collections;
using gov.va.medora.mdo;
using gov.va.medora.mdws.dto;
using gov.va.medora.mdo.dao.hl7.rxRefill;
using gov.va.medora.mdo.dao;
using gov.va.medora.mdo.api;

namespace gov.va.medora.mdws
{
    public class MedsLib
    {
        MySession mySession;

        public MedsLib() { }

        public MedsLib(MySession mySession)
        {
            this.mySession = mySession;
        }

        public TaggedMedicationArrays getOutpatientMeds()
        {
            TaggedMedicationArrays result = new TaggedMedicationArrays();

            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                IndexedHashtable t = Medication.getOutpatientMeds(mySession.ConnectionSet);
                result = new TaggedMedicationArrays(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        public TaggedMedicationArrays getIvMeds()
        {
            TaggedMedicationArrays result = new TaggedMedicationArrays();

            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                IndexedHashtable t = Medication.getIvMeds(mySession.ConnectionSet);
                result = new TaggedMedicationArrays(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        public TaggedMedicationArrays getImoMeds()
        {
            TaggedMedicationArrays result = new TaggedMedicationArrays();

            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                IndexedHashtable t = Medication.getImoMeds(mySession.ConnectionSet);
                result = new TaggedMedicationArrays(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        public TaggedMedicationArrays getUnitDoseMeds()
        {
            TaggedMedicationArrays result = new TaggedMedicationArrays();

            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                IndexedHashtable t = Medication.getUnitDoseMeds(mySession.ConnectionSet);
                result = new TaggedMedicationArrays(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        public TaggedMedicationArrays getOtherMeds()
        {
            TaggedMedicationArrays result = new TaggedMedicationArrays();

            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                IndexedHashtable t = Medication.getOtherMeds(mySession.ConnectionSet);
                result = new TaggedMedicationArrays(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        public TaggedMedicationArrays getAllMeds()
        {
            TaggedMedicationArrays result = new TaggedMedicationArrays();

            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                IndexedHashtable t = Medication.getAllMeds(mySession.ConnectionSet);
                result = new TaggedMedicationArrays(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        //public TaggedMedicationArrays getVaMeds()
        //{
        //    TaggedMedicationArrays result = new TaggedMedicationArrays();

        //    if (!mySession.cxns.IsAuthorized)
        //    {
        //        result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
        //    }
        //    if (result.fault != null)
        //    {
        //        return result;
        //    }

        //    try
        //    {
        //        MultiSourceQuery msq = new MultiSourceQuery(mySession.cxnMgr.Connections);
        //        MedsApi api = new MedsApi();
        //        IndexedHashtable t = api.getVaMeds(msq);
        //        result = new TaggedMedicationArrays(t);
        //    }
        //    catch (Exception e)
        //    {
        //        result.fault = new FaultTO(e);
        //    }
        //    return result;
        //}

        public TextTO getMedicationDetail(string siteId, string medId)
        {
            TextTO result = new TextTO();

            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            else if (siteId == "")
            {
                result.fault = new FaultTO("Missing siteId");
            }
            else if (medId == "")
            {
                result.fault = new FaultTO("Missing medId");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                string s = Medication.getMedicationDetail(mySession.ConnectionSet.getConnection(siteId), medId);
                result = new TextTO(s);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        public TaggedTextArray getImmunizations(string fromDate, string toDate, int nrpts)
        {
            TaggedTextArray result = new TaggedTextArray();

            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            else if (fromDate == "")
            {
                result.fault = new FaultTO("Missing fromDate");
            }
            else if (toDate == "")
            {
                result.fault = new FaultTO("Missing toDate");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                IndexedHashtable t = Medication.getImmunizations(mySession.ConnectionSet, fromDate, toDate, nrpts);
                result = new TaggedTextArray(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        public TaggedTextArray getOutpatientRxProfile()
        {
            TaggedTextArray result = new TaggedTextArray();

            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                IndexedHashtable t = Medication.getOutpatientRxProfile(mySession.ConnectionSet);
                result = new TaggedTextArray(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        public TaggedTextArray getMedsAdminHx(string fromDate, string toDate, int nrpts)
        {
            TaggedTextArray result = new TaggedTextArray();

            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            else if (fromDate == "")
            {
                result.fault = new FaultTO("Missing fromDate");
            }
            else if (toDate == "")
            {
                result.fault = new FaultTO("Missing toDate");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                IndexedHashtable t = Medication.getMedsAdminHx(mySession.ConnectionSet, fromDate, toDate, nrpts);
                result = new TaggedTextArray(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        public TaggedTextArray getMedsAdminLog(string fromDate, string toDate, int nrpts)
        {
            TaggedTextArray result = new TaggedTextArray();

            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            else if (fromDate == "")
            {
                result.fault = new FaultTO("Missing fromDate");
            }
            else if (toDate == "")
            {
                result.fault = new FaultTO("Missing toDate");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                IndexedHashtable t = Medication.getMedsAdminLog(mySession.ConnectionSet, fromDate, toDate, nrpts);
                result = new TaggedTextArray(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        public MedicationTO refillPrescription(string pwd, string sitecode, string mpiPid, string rxId)
        {
            MedicationTO result = new MedicationTO();

            if (String.IsNullOrEmpty(pwd))
            {
                result.fault = new FaultTO("Missing pwd");
            }
            else if (String.IsNullOrEmpty(sitecode))
            {
                result.fault = new FaultTO("Missing site ID");
            }
            else if (String.IsNullOrEmpty(mpiPid))
            {
                result.fault = new FaultTO("Missing patient ID");
            }
            else if (String.IsNullOrEmpty(rxId))
            {
                result.fault = new FaultTO("Missing Rx ID");
            }

            if (result.fault != null)
            {
                return result;
            }

            try
            {
                Site hl7Site = mySession.SiteTable.getSite(sitecode);
                DataSource hl7Src = null;
                foreach (DataSource src in hl7Site.Sources)
                {
                    if (String.Equals(src.Protocol, "HL7", StringComparison.CurrentCultureIgnoreCase))
                    {
                        hl7Src = src;
                        break;
                    }
                }
                if (hl7Src == null)
                {
                    throw new gov.va.medora.mdo.exceptions.MdoException("No HL7 data source in site table for that site ID");
                }
                HL7Connection cxn = new HL7Connection(hl7Src);
                cxn.connect();
                cxn.Pid = mpiPid;
                result = new MedicationTO(new MedsApi().refillPrescription(cxn, rxId));
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }

            return result;
        }

        public MedicationTO getRefillStatus(string sitecode, string rxId)
        {
            return new MedicationTO() { fault = new FaultTO(new NotImplementedException()) };
        }

        public TaggedMedicationArrays getPrescriptionsHL7(string pwd, string sitecode, string mpiPid)
        {
            TaggedMedicationArrays result = new TaggedMedicationArrays();

            if (String.IsNullOrEmpty(pwd))
            {
                result.fault = new FaultTO("Missing pwd");
            }
            else if (String.IsNullOrEmpty(sitecode))
            {
                result.fault = new FaultTO("Missing site ID");
            }
            else if (String.IsNullOrEmpty(mpiPid))
            {
                result.fault = new FaultTO("Missing patient ID");
            }

            if (result.fault != null)
            {
                return result;
            }

            try
            {
                Site hl7Site = mySession.SiteTable.getSite(sitecode);
                DataSource hl7Src = null;
                foreach (DataSource src in hl7Site.Sources)
                {
                    if (String.Equals(src.Protocol, "HL7", StringComparison.CurrentCultureIgnoreCase))
                    {
                        hl7Src = src;
                        break;
                    }
                }
                if (hl7Src == null)
                {
                    throw new gov.va.medora.mdo.exceptions.MdoException("No HL7 data source in site table for that site ID");
                }
                HL7Connection cxn = new HL7Connection(hl7Src);
                cxn.connect();
                cxn.Pid = mpiPid;
                result = new TaggedMedicationArrays(new MedsApi().getAllMeds(new ConnectionSet(cxn)));
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }

            return result;
        }
    }
}
