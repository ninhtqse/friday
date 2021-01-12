import React from 'react';
import axios from 'axios';
import {
    Container,
    Row,
    Col,
    Card, CardImg, CardBody,
    CardTitle, CardSubtitle, Button
} from 'reactstrap';
export default class Products extends React.Component{
    constructor(props){
        super(props);

        this.state = {
            products :[]
        }
    }
    componentDidMount() {
        axios.get(`https://5fed8c32595e420017c2c9e0.mockapi.io/api/v1/users`)
          .then(res => {
            const products = res.data;
            this.setState({ products:products });
          })
          .catch(error => console.log(error));
    }
    render(){
        const products = this.state.products;
        return (
            <Container>
                <Row>
                    {
                        products.map(product => (
                            <Col sm="3">
                            <Card>
                                <CardImg top width="100%" src={product.avatar} alt="Card image cap" />
                                <CardBody>
                                <CardTitle tag="h5">{product.name}</CardTitle>
                                <CardSubtitle tag="h6" className="mb-2 text-muted">{product.email}</CardSubtitle>
                                <Button>Button</Button>
                                </CardBody>
                            </Card>
                            </Col>
                        ))
                    }
                </Row>
            </Container>
        )
    }
}