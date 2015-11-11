using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo.dao.vista.fhie
{
    class FhieDaoFactory : AbstractDaoFactory
    {
        public override AbstractConnection getConnection(DataSource dataSource)
        {
            VistaConnection c = new VistaConnection(dataSource);
            c.ConnectStrategy = new VistaNatConnectStrategy(c);
            return c;
        }

        public override IToolsDao getToolsDao(AbstractConnection cxn)
        {
            throw new Exception("The method or operation is not implemented.");
        }

        public override IUserDao getUserDao(AbstractConnection cxn)
        {
            return new FhieUserDao(cxn);
        }

        public override IPatientDao getPatientDao(AbstractConnection cxn)
        {
            return new FhiePatientDao(cxn);
        }

        public override IClinicalDao getClinicalDao(AbstractConnection cxn)
        {
            return new FhieClinicalDao(cxn);
        }

        public override IEncounterDao getEncounterDao(AbstractConnection cxn)
        {
            return null;
        }

        public override IPharmacyDao getPharmacyDao(AbstractConnection cxn)
        {
            return new FhiePharmacyDao(cxn);
        }

        public override ILabsDao getLabsDao(AbstractConnection cxn)
        {
            return new FhieLabsDao(cxn);
        }

        public override INoteDao getNoteDao(AbstractConnection cxn)
        {
            return new FhieNoteDao(cxn);
        }

        public override IVitalsDao getVitalsDao(AbstractConnection cxn)
        {
            return new FhieVitalsDao(cxn);
        }

        public override IChemHemDao getChemHemDao(AbstractConnection cxn)
        {
            return new FhieChemHemDao(cxn);
        }

        public override IClaimsDao getClaimsDao(AbstractConnection cxn)
        {
            return null;
        }

        public override IConsultDao getConsultDao(AbstractConnection cxn)
        {
            return null;
        }

        public override IRemindersDao getRemindersDao(AbstractConnection cxn)
        {
            return null;
        }

        public override ILocationDao getLocationDao(AbstractConnection cxn)
        {
            return null;
        }

        public override IOrdersDao getOrdersDao(AbstractConnection cxn)
        {
            return null;
        }

        public override IRadiologyDao getRadiologyDao(AbstractConnection cxn)
        {
            return new VistaRadiologyDao(cxn);
        }


        public override ISchedulingDao getSchedulingDao(AbstractConnection cxn)
        {
            throw new NotImplementedException();
        }

        public override dao.IProblemDao getProblemDao(AbstractConnection cxn)
        {
            throw new NotImplementedException();
        }
    }
}
