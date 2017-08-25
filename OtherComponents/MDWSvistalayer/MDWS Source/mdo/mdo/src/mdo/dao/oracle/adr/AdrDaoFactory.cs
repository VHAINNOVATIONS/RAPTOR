using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo.dao.oracle.adr
{
    public class AdrDaoFactory : AbstractDaoFactory
    {
        public override AbstractConnection getConnection(DataSource dataSource)
        {
            return new MdoOracleConnection(dataSource);
        }

        public override IClaimsDao getClaimsDao(AbstractConnection cxn)
        {
            return new AdrClaimsDao(cxn);
        }

        public override IUserDao getUserDao(AbstractConnection cxn)
        {
            throw new NotImplementedException();
        }

        public override IPatientDao getPatientDao(AbstractConnection cxn)
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
