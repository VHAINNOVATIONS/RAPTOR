using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo.dao.vista
{
    class VistaDaoFactory : AbstractDaoFactory
    {
        public override AbstractConnection getConnection(DataSource dataSource)
        {
            VistaConnection c = new VistaConnection(dataSource);

            // Removed by Joe 6/21/10.  Not all sites have the new broker.
            c.ConnectStrategy = new VistaNatConnectStrategy(c);

            //c.ConnectStrategy = new VistaDirectConnectStrategy(c);
            return c;
        }

        public override IToolsDao getToolsDao(AbstractConnection cxn)
        {
            return new VistaToolsDao(cxn); ;
        }

        public override IUserDao getUserDao(AbstractConnection cxn)
        {
            return new VistaUserDao(cxn);
        }

        public override IPatientDao getPatientDao(AbstractConnection cxn)
        {
            return new VistaPatientDao(cxn);
        }

        public override IClinicalDao getClinicalDao(AbstractConnection cxn)
        {
            return new VistaClinicalDao(cxn);
        }

        public override IEncounterDao getEncounterDao(AbstractConnection cxn)
        {
            return new VistaEncounterDao(cxn);
        }

        public override IPharmacyDao getPharmacyDao(AbstractConnection cxn)
        {
            return new VistaPharmacyDao(cxn);
        }

        public override ILabsDao getLabsDao(AbstractConnection cxn)
        {
            return new VistaLabsDao(cxn);
        }

        public override INoteDao getNoteDao(AbstractConnection cxn)
        {
            return new VistaNoteDao(cxn);
        }

        public override IVitalsDao getVitalsDao(AbstractConnection cxn)
        {
            return new VistaVitalsDao(cxn);
        }

        public override IChemHemDao getChemHemDao(AbstractConnection cxn)
        {
            return new VistaChemHemDao(cxn);
        }

        public override IClaimsDao getClaimsDao(AbstractConnection cxn)
        {
            return new VistaClaimsDao(cxn);
        }

        public override IConsultDao getConsultDao(AbstractConnection cxn)
        {
            return new VistaConsultDao(cxn);
        }

        public override IRemindersDao getRemindersDao(AbstractConnection cxn)
        {
            return new VistaRemindersDao(cxn);
        }

        public override ILocationDao getLocationDao(AbstractConnection cxn)
        {
            return new VistaLocationDao(cxn);
        }

        public override IOrdersDao getOrdersDao(AbstractConnection cxn)
        {
            return new VistaOrdersDao(cxn);
        }

        public override IRadiologyDao getRadiologyDao(AbstractConnection cxn)
        {
            return new VistaRadiologyDao(cxn);
        }

        public override ISchedulingDao getSchedulingDao(AbstractConnection cxn)
        {
            return new VistaSchedulingDao(cxn);
        }

        public override dao.IProblemDao getProblemDao(AbstractConnection cxn)
        {
            return new VistaProblemDao(cxn);
        }
    }
}
