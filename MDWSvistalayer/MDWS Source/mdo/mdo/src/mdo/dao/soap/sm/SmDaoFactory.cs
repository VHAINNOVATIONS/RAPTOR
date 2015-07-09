using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.dao.soap.sm
{
    public class SmDaoFactory : AbstractDaoFactory
    {
        public SmDaoFactory() { }

        public override AbstractConnection getConnection(DataSource dataSource)
        {
            return new SmConnection(dataSource);
        }

        public override IUserDao getUserDao(AbstractConnection cxn)
        {
            throw new NotImplementedException();
        }

        public override IPatientDao getPatientDao(AbstractConnection cxn)
        {
            return new SmPatientDao(cxn);
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

        public override IClaimsDao getClaimsDao(AbstractConnection cxn)
        {
            throw new NotImplementedException();
        }

        public override IConsultDao getConsultDao(AbstractConnection cxn)
        {
            throw new NotImplementedException();
        }

        public override IRemindersDao getRemindersDao(AbstractConnection cxn)
        {
            throw new NotImplementedException();
        }

        public override ILocationDao getLocationDao(AbstractConnection cxn)
        {
            throw new NotImplementedException();
        }

        public override IOrdersDao getOrdersDao(AbstractConnection cxn)
        {
            throw new NotImplementedException();
        }

        public override IRadiologyDao getRadiologyDao(AbstractConnection cxn)
        {
            throw new NotImplementedException();
        }

        public override ISchedulingDao getSchedulingDao(AbstractConnection cxn)
        {
            throw new NotImplementedException();
        }
    }
}
