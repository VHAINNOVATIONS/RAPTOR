using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo.dao.vista.fhie
{
    public class FhiePharmacyDao : IPharmacyDao
    {
        VistaPharmacyDao vistaDao = null;

        public FhiePharmacyDao(AbstractConnection cxn)
        {
            vistaDao = new VistaPharmacyDao(cxn);
        }

        public Medication[] getOutpatientMeds()
        {
            return vistaDao.getOutpatientMeds();
        }

        public Medication[] getIvMeds(string pid)
        {
            return vistaDao.getIvMedsRdv(pid);
        }

        public Medication[] getIvMeds()
        {
            return vistaDao.getIvMedsRdv();
        }

        public Medication[] getUnitDoseMeds(string pid)
        {
            return vistaDao.getUnitDoseMeds(pid);
        }

        public Medication[] getUnitDoseMeds()
        {
            return vistaDao.getUnitDoseMeds();
        }

        public Medication[] getOtherMeds(string pid)
        {
            return vistaDao.getOtherMeds(pid);
        }

        public Medication[] getOtherMeds()
        {
            return vistaDao.getOtherMeds();
        }

        public Medication[] getAllMeds(string pid)
        {
            return vistaDao.getAllMeds(pid);
        }

        public Medication[] getAllMeds()
        {
            return vistaDao.getAllMeds();
        }

        public Medication[] getVaMeds()
        {
            return null;
        }

        public Medication[] getVaMeds(string pid)
        {
            return null;
        }

        public string getMedicationDetail(string medId) 
        {
            return null;
        }

        public string getOutpatientRxProfile()
        {
            return null;
        }

        public string getMedsAdminHx(string fromDate, string toDate, int nrpts)
        {
            return null;
        }

        public string getMedsAdminLog(string fromDate, string toDate, int nrpts)
        {
            return null;
        }

        public string getImmunizations(string fromDate, string toDate, int nrpts)
        {
            return null;
        }

        public Medication[] getInpatientForOutpatientMeds()
        {
            return vistaDao.getInpatientForOutpatientMeds();
        }

        public Medication[] getInpatientForOutpatientMeds(string pid)
        {
            return vistaDao.getInpatientForOutpatientMeds(pid);
        }


        public Medication refillPrescription(string rxId)
        {
            throw new NotImplementedException();
        }
    }
}
