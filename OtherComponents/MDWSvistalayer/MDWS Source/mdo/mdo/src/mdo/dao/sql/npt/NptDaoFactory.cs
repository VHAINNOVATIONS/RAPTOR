using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo.dao.sql.npt
{
    public class NptDaoFactory : AbstractDaoFactory
    {
        public override AbstractConnection getConnection(DataSource dataSource)
        {
            return new NptConnection(dataSource);
        }

        public override IClaimsDao getClaimsDao(AbstractConnection cxn)
        {
            return new NptClaimsDao(cxn);
        }

        public override IPatientDao getPatientDao(AbstractConnection cxn)
        {
            return new NptPatientDao(cxn);
        }

        public override IUserDao getUserDao(AbstractConnection cxn)
        {
            throw new NotImplementedException();
        }

        public override IClinicalDao getClinicalDao(AbstractConnection cxn)
        {
            throw new NotImplementedException();
        }

        public override IEncounterDao getEncounterDao(AbstractConnection cxn)
        {
            throw new NotImplementedException();
        }

        public override IPharmacyDao getPharmacyDao(AbstractConnection cxn)
        {
            throw new NotImplementedException();
        }

        public override ILabsDao getLabsDao(AbstractConnection cxn)
        {
            throw new NotImplementedException();
        }

        public override IToolsDao getToolsDao(AbstractConnection cxn)
        {
            throw new NotImplementedException();
        }

        public override INoteDao getNoteDao(AbstractConnection cxn)
        {
            throw new NotImplementedException();
        }

        public override IVitalsDao getVitalsDao(AbstractConnection cxn)
        {
            throw new NotImplementedException();
        }

        public override IChemHemDao getChemHemDao(AbstractConnection cxn)
        {
            throw new NotImplementedException();
        }

        public override IConsultDao getConsultDao(AbstractConnection cxn)
        {
            throw new NotImplementedException();
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
            return null;
        }

        public override ISchedulingDao getSchedulingDao(AbstractConnection cxn)
        {
            throw new NotImplementedException();
        }

        public override IProblemDao getProblemDao(AbstractConnection cxn)
        {
            throw new NotImplementedException();
        }
    }
}
