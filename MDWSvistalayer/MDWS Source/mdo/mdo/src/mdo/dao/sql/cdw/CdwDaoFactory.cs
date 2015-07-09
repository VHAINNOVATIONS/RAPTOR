using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.dao.sql.cdw
{
    public class CdwDaoFactory : AbstractDaoFactory
    {
        public CdwDaoFactory()
        {

        }

        public override AbstractConnection getConnection(DataSource dataSource)
        {
            return new CdwConnection(dataSource);
        }

        public override IUserDao getUserDao(AbstractConnection cxn)
        {
            throw new NotImplementedException();
        }

        public override IPatientDao getPatientDao(AbstractConnection cxn)
        {
            return new CdwPatientDao(cxn);
        }

        public override IClinicalDao getClinicalDao(AbstractConnection cxn)
        {
            return new CdwClinicalDao(cxn);
        }

        public override IEncounterDao getEncounterDao(AbstractConnection cxn)
        {
            return new CdwEncounterDao(cxn);
        }

        public override IPharmacyDao getPharmacyDao(AbstractConnection cxn)
        {
            return new CdwPharmacyDao(cxn);
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
            return new CdwVitalsDao(cxn);
        }

        public override IChemHemDao getChemHemDao(AbstractConnection cxn)
        {
            return new CdwChemHemDao(cxn);
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
            return new CdwLocationDao(cxn);
        }

        public override IOrdersDao getOrdersDao(AbstractConnection cxn)
        {
            throw new NotImplementedException();
        }

        public override IRadiologyDao getRadiologyDao(AbstractConnection cxn)
        {
            return new CdwRadiologyDao(cxn);
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
